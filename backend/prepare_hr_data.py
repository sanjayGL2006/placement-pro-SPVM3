# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
import sys

def prepare_data(input_file, output_file):
    print(f"Reading {input_file}...")
    try:
        with open(input_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
    except Exception as e:
        print(f"Failed to load JSON: {e}")
        sys.exit(1)
        
    print(f"Found {len(data)} records. Formatting to JSONL...")
    
    with open(output_file, 'w', encoding='utf-8') as f:
        for item in data:
            # Assuming the dataset has 'question' and 'answer' or 'context'
            # Adjust the keys based on the exact JSON schema of archive (1)
            q = item.get('question', '')
            a = item.get('answer', '')
            
            # Format as prompt-completion pair for causal LM fine-tuning
            if q and a:
                json_record = {
                    "text": f"### Human: {q}\n### Assistant: {a}"
                }
                f.write(json.dumps(json_record) + "\n")
                
    print(f"Done. Saved ready-to-train data to {output_file}")

if __name__ == "__main__":
    INPUT_PATH = r"..\dataset\archive (1)\hr_interview_questions_dataset.json"
    OUTPUT_PATH = "hr_qa_prepared.jsonl"
    prepare_data(INPUT_PATH, OUTPUT_PATH)
