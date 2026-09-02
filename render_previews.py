import os
import re

frontend_dir = r"c:\Users\Sanjay G L\Desktop\placement-pro\frontend"
output_dir = os.path.join(frontend_dir, "previews")
os.makedirs(output_dir, exist_ok=True)

# Read partials
with open(os.path.join(frontend_dir, "partials", "sidebar.php"), "r", encoding="utf-8") as f:
    sidebar_content = f.read()

with open(os.path.join(frontend_dir, "partials", "header.php"), "r", encoding="utf-8") as f:
    header_content = f.read()

with open(os.path.join(frontend_dir, "partials", "nav.php"), "r", encoding="utf-8") as f:
    nav_content = f.read()

# Replace PHP tags in sidebar
sidebar_html = re.sub(r"<\?php.*?\?>", "", sidebar_content, flags=re.DOTALL)
# Replace PHP tags in header
header_html = re.sub(r"<\?php.*?\?>", "", header_content, flags=re.DOTALL)
header_html = header_html.replace('<?php echo htmlspecialchars($userName); ?>', 'Admin User')
header_html = header_html.replace('<?php echo htmlspecialchars($userRole); ?>', 'Administrator')

# Replace PHP tags in nav
nav_html = nav_content.replace("<?php\nrequire_once __DIR__ . '/sidebar.php';\nrequire_once __DIR__ . '/header.php';\n?>", sidebar_html + "\n" + header_html)

files = ['login.php', 'dashboard.php', 'sections.php', 'students.php', 'companies.php', 'import.php', 'reports.php', 'settings.php', 'push.php', 'skill_gap.php', 'ai_hub.php', 'documents.php']


for file in files:
    filepath = os.path.join(frontend_dir, file)
    if not os.path.exists(filepath):
        continue
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Replace php require/login header
    content = re.sub(r"<\?php require_once 'config.php';.*?\?>", "", content)
    content = re.sub(r"<\?php require_once 'config.php'; \?>", "", content)
    content = content.replace("<?php include 'partials/nav.php'; ?>", nav_html)
    
    # Replace login script block for static preview
    if file == 'login.php':
        login_script = """document.getElementById('loginForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value || 'admin@college.edu';
      localStorage.setItem('token', 'demo_admin_token');
      localStorage.setItem('user', JSON.stringify({ name: 'SPVM3 Tech Solution by Sanjay G L', role: 'admin', email: email }));
      window.location.href = 'dashboard.html';
    });"""
        content = re.sub(r"document\.getElementById\('loginForm'\)\.addEventListener\('submit',[\s\S]*?\n    \}\);", login_script, content)


    # Replace .php links with .html links for static GitHub Pages preview navigation
    for page_name in ['dashboard', 'students', 'companies', 'import', 'sections', 'reports', 'settings', 'login', 'logout', 'push', 'skill_gap', 'ai_hub', 'documents']:
        content = content.replace(f'href="{page_name}.php"', f'href="{page_name}.html"')
        content = content.replace(f"href='{page_name}.php'", f"href='{page_name}.html'")


    content = content.replace("<?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Admin User'); ?>", "SPVM3 Tech Solution by Sanjay G L")
    content = content.replace("<?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'SPVM3 Tech Solution by Sanjay G L'); ?>", "SPVM3 Tech Solution by Sanjay G L")
    content = content.replace("<?php echo htmlspecialchars($_SESSION['user']['email'] ?? 'admin@university.edu'); ?>", "admin@university.edu")
    
    # Fix relative paths to css/js if opened in previews/
    content = content.replace('assets/css/style.css', '../assets/css/style.css')
    content = content.replace('assets/js/api.js', '../assets/js/api.js')
    content = content.replace('assets/vendor/sweetalert2/sweetalert2.all.min.js', '../assets/vendor/sweetalert2/sweetalert2.all.min.js')

    
    out_name = file.replace('.php', '.html')
    with open(os.path.join(output_dir, out_name), "w", encoding="utf-8") as f:
        f.write(content)


print("Rendered preview HTML files successfully!")
