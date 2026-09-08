# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
import spacy
# pyrefly: ignore [missing-import]
from spacy.tokens import DocBin
# pyrefly: ignore [missing-import]
from spacy.util import filter_spans
# pyrefly: ignore [missing-import]
import os

# Paths
DATA_PATH = "../../dataset/archive (2)/train/train_data.json"
OUTPUT_DIR = "./output_ner"
TRAIN_SPACY = os.path.join(OUTPUT_DIR, "train.spacy")

def load_data(file_path):
    with open(file_path, "r", encoding="utf-8") as f:
        return json.load(f)

def convert_to_spacy(data, output_path):
    nlp = spacy.blank("en")
    doc_bin = DocBin()
    
    for item in data:
        # Expected format: {"text": "...", "labels": [[start, end, "LABEL"], ...]}
        # Or {"content": "...", "annotation": [{"label": ["LABEL"], "points": [{"start": 0, "end": 5, "text": "..."}]}]}
        # We will handle a generic format assuming list of [text, {"entities": [[start, end, label]]}]
        
        # If the dataset has a different format, you might need to adapt this parsing logic.
        if isinstance(item, list) and len(item) == 2:
            text = item[0]
            annotations = item[1].get("entities", [])
        elif isinstance(item, dict) and "content" in item:
            text = item["content"]
            annotations = []
            for ann in item.get("annotation", []):
                if ann.get("points") and ann.get("label"):
                    start = ann["points"][0]["start"]
                    end = ann["points"][0]["end"] + 1
                    label = ann["label"][0]
                    annotations.append((start, end, label))
        else:
            print("Unknown data format, skipping...")
            continue
            
        doc = nlp.make_doc(text)
        ents = []
        for start, end, label in annotations:
            span = doc.char_span(start, end, label=label, alignment_mode="contract")
            if span is not None:
                ents.append(span)
                
        # Filter overlapping spans
        ents = filter_spans(ents)
        doc.ents = ents
        doc_bin.add(doc)
        
    doc_bin.to_disk(output_path)
    print(f"Saved to {output_path}")

def main():
    if not os.path.exists(OUTPUT_DIR):
        os.makedirs(OUTPUT_DIR)
        
    if not os.path.exists(DATA_PATH):
        print(f"Data not found at {DATA_PATH}. Please check the path.")
        return
        
    print("Loading data...")
    data = load_data(DATA_PATH)
    print("Converting to spaCy format...")
    convert_to_spacy(data, TRAIN_SPACY)
    
    print("\nNext steps to train the model:")
    print("1. Create a config.cfg file using: python -m spacy init config config.cfg --lang en --pipeline ner")
    print(f"2. Train the model using: python -m spacy train config.cfg --output {OUTPUT_DIR} --paths.train {TRAIN_SPACY} --paths.dev {TRAIN_SPACY}")

if __name__ == "__main__":
    main()
