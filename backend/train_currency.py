# pyrefly: ignore [missing-import]
import os
# pyrefly: ignore [missing-import]
import torch
# pyrefly: ignore [missing-import]
import torch.nn as nn
# pyrefly: ignore [missing-import]
import torch.optim as optim
# pyrefly: ignore [missing-import]
from torchvision import datasets, transforms, models
# pyrefly: ignore [missing-import]
from torch.utils.data import DataLoader

def get_data_loaders(data_dir, batch_size=32):
    # Standard image transformations for ResNet
    transform = transforms.Compose([
        transforms.Resize((224, 224)),
        transforms.ToTensor(),
        transforms.Normalize(mean=[0.485, 0.456, 0.406], 
                             std=[0.229, 0.224, 0.225])
    ])
    
    # Assuming 'archive' has folders representing classes 
    # e.g., archive/Fake Notes/, archive/2000_dataset/
    dataset = datasets.ImageFolder(data_dir, transform=transform)
    
    # Split into train and validation (80-20 split)
    train_size = int(0.8 * len(dataset))
    val_size = len(dataset) - train_size
    train_dataset, val_dataset = torch.utils.data.random_split(dataset, [train_size, val_size])
    
    train_loader = DataLoader(train_dataset, batch_size=batch_size, shuffle=True)
    val_loader = DataLoader(val_dataset, batch_size=batch_size, shuffle=False)
    
    return train_loader, val_loader, dataset.classes

def train_model(data_dir, num_epochs=5):
    device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
    print(f"Using device: {device}")
    
    train_loader, val_loader, classes = get_data_loaders(data_dir)
    print(f"Classes found: {classes}")
    
    # Load pre-trained ResNet18
    model = models.resnet18(pretrained=True)
    
    # Freeze earlier layers
    for param in model.parameters():
        param.requires_grad = False
        
    # Replace the classification head
    num_ftrs = model.fc.in_features
    model.fc = nn.Linear(num_ftrs, len(classes))
    model = model.to(device)
    
    criterion = nn.CrossEntropyLoss()
    optimizer = optim.Adam(model.fc.parameters(), lr=0.001)
    
    for epoch in range(num_epochs):
        print(f"\nEpoch {epoch+1}/{num_epochs}")
        print("-" * 10)
        
        # Training Phase
        model.train()
        running_loss = 0.0
        running_corrects = 0
        
        for inputs, labels in train_loader:
            inputs, labels = inputs.to(device), labels.to(device)
            
            optimizer.zero_grad()
            outputs = model(inputs)
            loss = criterion(outputs, labels)
            _, preds = torch.max(outputs, 1)
            
            loss.backward()
            optimizer.step()
            
            running_loss += loss.item() * inputs.size(0)
            running_corrects += torch.sum(preds == labels.data)
            
        epoch_loss = running_loss / len(train_loader.dataset)
        # pyrefly: ignore [missing-attribute]
        epoch_acc = running_corrects.double() / len(train_loader.dataset)
        print(f"Train Loss: {epoch_loss:.4f} Acc: {epoch_acc:.4f}")
        
    print("Saving model weights...")
    torch.save(model.state_dict(), "currency_classifier.pth")
    print("Training Complete!")

if __name__ == "__main__":
    # Point to the image archive
    DATASET_DIR = r"..\dataset\archive"
    if not os.path.exists(DATASET_DIR):
        print(f"Dataset directory not found: {DATASET_DIR}")
    else:
        train_model(DATASET_DIR)
