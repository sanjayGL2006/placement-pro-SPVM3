import sys
import os
import pandas as pd

# Add backend directory to sys.path so we can import import_utils
sys.path.append(r"c:\Users\Sanjay G L\Desktop\placement-pro\backend")

from import_utils import build_preview

# Mock the DataFrame matching the user's Excel schema screenshot
mock_df = pd.DataFrame([
    {
        "STUDENT": "vijay",
        "DEPARTMENT & SECTION": "bca ,section C",
        "Roll No": "21CS101",
        "STATUS": "selected"
    },
    {
        "STUDENT": "raghu",
        "DEPARTMENT & SECTION": "bca ,section a",
        "Roll No": "21CS102",
        "STATUS": "applied"
    },
    {
        "STUDENT": "lothith",
        "DEPARTMENT & SECTION": "bca section a", # Space instead of comma
        "Roll No": "21CS103",
        "STATUS": "not_placed"
    }
])

print("Columns in mock dataframe before build_preview:")
print(list(mock_df.columns))

# Call build_preview
preview = build_preview(mock_df, "student")

print("\nColumns mapped successfully:")
print(preview["column_mapping"])

print("\nParsed and normalized rows:")
for r in preview["rows"]:
    print(r)
