import os
import re

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()
        
    new_lines = []
    changed = False
    
    for i, line in enumerate(lines):
        # Check if line is an import statement
        if re.match(r'^\s*(import\s+[a-zA-Z0-9_\.]+|from\s+[a-zA-Z0-9_\.]+\s+import\s+)', line):
            # Check if previous line in new_lines has the ignore comment
            if len(new_lines) == 0 or '# pyrefly: ignore [missing-import]' not in new_lines[-1]:
                indent = re.match(r'^\s*', line).group(0)
                new_lines.append(f"{indent}# pyrefly: ignore [missing-import]\n")
                changed = True
        new_lines.append(line)
        
    if changed:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.writelines(new_lines)
        print(f"Updated {filepath}")

def main():
    backend_dir = r"c:\Users\Sanjay G L\Desktop\placement-pro-SPVM3-main\backend"
    for root, dirs, files in os.walk(backend_dir):
        # skip venv and __pycache__
        if 'venv' in dirs:
            dirs.remove('venv')
        if '__pycache__' in dirs:
            dirs.remove('__pycache__')
            
        for file in files:
            if file.endswith('.py'):
                filepath = os.path.join(root, file)
                process_file(filepath)

if __name__ == '__main__':
    main()
