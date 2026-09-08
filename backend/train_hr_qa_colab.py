"""
Colab Notebook Script for Training HR Interview Generator

Instructions for Colab:
1. Upload `hr_qa_prepared.jsonl` to Colab
2. Install dependencies:
   !pip install -q -U transformers peft accelerate datasets trl bitsandbytes
3. Run this script
"""

# pyrefly: ignore [missing-import]
import torch
# pyrefly: ignore [missing-import]
from datasets import load_dataset
# pyrefly: ignore [missing-import]
from transformers import (
    AutoModelForCausalLM,
    AutoTokenizer,
    TrainingArguments
)
# pyrefly: ignore [missing-import]
from peft import LoraConfig, get_peft_model
# pyrefly: ignore [missing-import]
from trl import SFTTrainer

# We use a small lightweight base model for demonstration
MODEL_NAME = "distilgpt2" 

def main():
    print("Loading prepared dataset...")
    dataset = load_dataset('json', data_files='hr_qa_prepared.jsonl', split='train')
    
    print("Loading tokenizer and model...")
    tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
    tokenizer.pad_token = tokenizer.eos_token
    
    model = AutoModelForCausalLM.from_pretrained(
        MODEL_NAME, 
        device_map="auto"
    )
    
    # Configure LoRA (Low-Rank Adaptation)
    lora_config = LoraConfig(
        r=8,
        lora_alpha=32,
        target_modules=["c_attn"], # Adjust based on model architecture
        lora_dropout=0.05,
        bias="none",
        task_type="CAUSAL_LM"
    )
    
    model = get_peft_model(model, lora_config)
    model.print_trainable_parameters()
    
    training_args = TrainingArguments(
        output_dir="./hr_qa_model_output",
        per_device_train_batch_size=4,
        gradient_accumulation_steps=4,
        learning_rate=2e-4,
        logging_steps=10,
        max_steps=500, # Adjust based on dataset size
        save_steps=100,
        fp16=True, # Use Mixed Precision if GPU supports it
    )
    
    trainer = SFTTrainer(
        model=model,
        train_dataset=dataset,
        dataset_text_field="text",
        max_seq_length=256,
        args=training_args
    )
    
    print("Starting training...")
    trainer.train()
    
    print("Saving the final adapter...")
    trainer.model.save_pretrained("hr_qa_model_final")
    print("Done!")

if __name__ == "__main__":
    main()
