# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
import os

# Paths
INPUT_PATH = "../../dataset/archive (1)/hr_interview_questions_dataset.json"
OUTPUT_PATH = "./hr_data.jsonl"

def process_large_json():
    """
    Since the file is 1.2GB, we should avoid loading it all into memory if possible.
    However, if it's a single JSON array, we can use an iterative parser like ijson,
    or if we have enough RAM (which we usually do for 1.2GB), we can load it.
    For safety, let's load it assuming standard JSON format and write to JSONL.
    """
    if not os.path.exists(INPUT_PATH):
        print(f"File not found: {INPUT_PATH}")
        return

    print("Loading JSON dataset (this may take a while)...")
    try:
        with open(INPUT_PATH, "r", encoding="utf-8") as f:
            data = json.load(f)
            
        print(f"Loaded {len(data)} records. Formatting to JSONL...")
        
        with open(OUTPUT_PATH, "w", encoding="utf-8") as out_f:
            for item in data:
                # Assuming the dataset has 'question' and 'answer' or 'text' fields
                # We format it into instruction-following format
                instruction = item.get("question", item.get("instruction", ""))
                response = item.get("answer", item.get("response", ""))
                context = item.get("context", "")
                
                # Format for Llama 3 / generic chat completion
                prompt = f"System: You are an expert HR Interview assistant.\nUser: {instruction}\n"
                if context:
                    prompt += f"Context: {context}\n"
                prompt += f"Assistant: {response}"
                
                record = {"text": prompt}
                out_f.write(json.dumps(record) + "\n")
                
        print(f"Successfully wrote data to {OUTPUT_PATH}")
        
    except Exception as e:
        print(f"Error processing dataset: {e}")
        print("Tip: If you run out of memory, install and use the 'ijson' library to stream the file.")

if __name__ == "__main__":
    process_large_json()
