# pyrefly: ignore [missing-import]
import json
# pyrefly: ignore [missing-import]
import spacy
# pyrefly: ignore [missing-import]
from spacy.tokens import DocBin
# pyrefly: ignore [missing-import]
from spacy.training import Example
# pyrefly: ignore [missing-import]
import random
# pyrefly: ignore [missing-import]
import os

def load_data(filepath):
    print(f"Loading data from {filepath}...")
    with open(filepath, 'r', encoding='utf-8') as f:
        data = json.load(f)
    return data

def prepare_spacy_data(data):
    """
    Convert the raw JSON array format: 
    [{"text": "...", "entities": [[start, end, label], ...]}, ...]
    to spaCy format:
    [(text, {"entities": [(start, end, label), ...]}), ...]
    """
    spacy_data = []
    for item in data:
        text = item['text']
        entities = []
        for start, end, label in item['entities']:
            # Spacy requires strict bounds; sometimes datasets have overlapping or out-of-bound entities.
            # We add them to a list for filtering.
            entities.append((start, end, label))
        
        spacy_data.append((text, {"entities": entities}))
    return spacy_data

def train_ner_model(train_data, output_dir="models/ner_model", iterations=10):
    """
    Train a custom NER model using spaCy.
    """
    print("Setting up spaCy blank English model...")
    nlp = spacy.blank("en")
    
    # Add the NER component to the pipeline
    if "ner" not in nlp.pipe_names:
        ner = nlp.add_pipe("ner", last=True)
    else:
        ner = nlp.get_pipe("ner")
    
    # Add labels to the NER component
    for _, annotations in train_data:
        for ent in annotations.get("entities"):
            # pyrefly: ignore [missing-attribute]
            ner.add_label(ent[2])
    
    # Disable other pipes during training if they existed (blank has none)
    other_pipes = [pipe for pipe in nlp.pipe_names if pipe != "ner"]
    
    print(f"Starting training for {iterations} iterations...")
    with nlp.disable_pipes(*other_pipes):
        optimizer = nlp.begin_training()
        for itn in range(iterations):
            random.shuffle(train_data)
            losses = {}
            # Batch the examples and iterate over them
            for batch in spacy.util.minibatch(train_data, size=2):
                for text, annotations in batch:
                    # Filter out overlapping/invalid entities
                    doc = nlp.make_doc(text)
                    try:
                        example = Example.from_dict(doc, annotations)
                        nlp.update([example], drop=0.5, losses=losses, sgd=optimizer)
                    except ValueError as e:
                        # Skip if there's a misalignment in character indices
                        continue
            print(f"Iteration {itn + 1} Losses:", losses)
            
    print(f"Saving model to {output_dir}...")
    os.makedirs(output_dir, exist_ok=True)
    nlp.to_disk(output_dir)
    print("Training complete!")

if __name__ == "__main__":
    BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    DATASET_PATH = os.path.join(BASE_DIR, "dataset", "archive (2)", "train", "train_data.json")
    if not os.path.exists(DATASET_PATH):
        print(f"Dataset not found at: {DATASET_PATH}")
        print("Please ensure the path is correct.")
    else:
        raw_data = load_data(DATASET_PATH)
        # For memory/time constraints in testing, we might limit the training set
        # raw_data = raw_data[:100] 
        
        spacy_training_data = prepare_spacy_data(raw_data)
        train_ner_model(spacy_training_data)
