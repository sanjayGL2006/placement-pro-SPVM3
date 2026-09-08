"""
This script is designed to be run in Google Colab or an environment with a strong GPU.
It fine-tunes a large language model (e.g., Llama-3 8B or Mistral 7B) using LoRA.
"""

# pyrefly: ignore [missing-import]
import os
# pyrefly: ignore [missing-import]
import torch
# pyrefly: ignore [missing-import]
from datasets import load_dataset
# pyrefly: ignore [missing-import]
from transformers import (
    AutoModelForCausalLM,
    AutoTokenizer,
    BitsAndBytesConfig,
    TrainingArguments
)
# pyrefly: ignore [missing-import]
from peft import LoraConfig, get_peft_model, prepare_model_for_kbit_training
# pyrefly: ignore [missing-import]
from trl import SFTTrainer

# Configuration
MODEL_ID = "NousResearch/Llama-2-7b-chat-hf" # Replace with "meta-llama/Meta-Llama-3-8B-Instruct" if you have access
DATA_PATH = "./hr_data.jsonl"
OUTPUT_DIR = "./hr_qa_model"

def train():
    if not os.path.exists(DATA_PATH):
        print(f"Data not found at {DATA_PATH}. Run prepare_hr_data.py first.")
        return

    print("Loading dataset...")
    dataset = load_dataset("json", data_files=DATA_PATH, split="train")

    # 4-bit Quantization Config (saves VRAM)
    bnb_config = BitsAndBytesConfig(
        load_in_4bit=True,
        bnb_4bit_quant_type="nf4",
        bnb_4bit_compute_dtype=torch.float16,
    )

    print("Loading model and tokenizer...")
    model = AutoModelForCausalLM.from_pretrained(
        MODEL_ID,
        quantization_config=bnb_config,
        device_map="auto",
    )
    model = prepare_model_for_kbit_training(model)

    tokenizer = AutoTokenizer.from_pretrained(MODEL_ID)
    tokenizer.pad_token = tokenizer.eos_token

    # LoRA Config
    peft_config = LoraConfig(
        r=16,
        lora_alpha=32,
        lora_dropout=0.05,
        bias="none",
        task_type="CAUSAL_LM"
    )
    
    model = get_peft_model(model, peft_config)

    # Training Arguments
    training_args = TrainingArguments(
        output_dir=OUTPUT_DIR,
        per_device_train_batch_size=4,
        gradient_accumulation_steps=4,
        optim="paged_adamw_32bit",
        save_steps=100,
        logging_steps=10,
        learning_rate=2e-4,
        fp16=True,
        max_grad_norm=0.3,
        max_steps=500, # Adjust based on dataset size
        warmup_ratio=0.03,
        group_by_length=True,
        lr_scheduler_type="constant",
    )

    print("Initializing trainer...")
    trainer = SFTTrainer(
        model=model,
        train_dataset=dataset,
        peft_config=peft_config,
        dataset_text_field="text", # The field name in JSONL
        max_seq_length=512,
        tokenizer=tokenizer,
        args=training_args,
    )

    print("Starting training...")
    trainer.train()
    
    print("Saving model...")
    trainer.model.save_pretrained(os.path.join(OUTPUT_DIR, "final_adapter"))
    tokenizer.save_pretrained(os.path.join(OUTPUT_DIR, "final_adapter"))
    print("Training complete!")

if __name__ == "__main__":
    train()
