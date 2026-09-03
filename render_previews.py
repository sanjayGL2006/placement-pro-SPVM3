"""Convert frontend PHP pages into static HTML for GitHub Pages / Vercel."""
import os
import re
import shutil

ROOT = os.path.dirname(os.path.abspath(__file__))
FRONTEND = os.path.join(ROOT, "frontend")
OUTPUT = os.path.join(FRONTEND, "previews")
PAGES = [
    "login.php",
    "dashboard.php",
    "sections.php",
    "students.php",
    "companies.php",
    "import.php",
    "reports.php",
    "settings.php",
    "push.php",
    "skill_gap.php",
    "ai_hub.php",
    "documents.php",
    "company_dashboard.php",
]
PHP_TO_HTML = [
    "dashboard",
    "students",
    "companies",
    "import",
    "sections",
    "reports",
    "settings",
    "login",
    "logout",
    "push",
    "skill_gap",
    "ai_hub",
    "documents",
    "company_dashboard",
]
ASSET_REWRITES = [
    ("assets/vendor/sweetalert2/sweetalert2.all.min.js", "../assets/vendor/sweetalert2/sweetalert2.all.min.js"),
    ("assets/js/firebase-init.js", "../assets/js/firebase-init.js"),
    ("assets/js/auth.js", "../assets/js/auth.js"),
    ("assets/js/api.js", "../assets/js/api.js"),
    ("assets/css/style.css", "../assets/css/style.css"),
    ('href="favicon.svg"', 'href="../assets/favicon.svg"'),
    ('href="favicon.ico"', 'href="../assets/favicon.ico"'),
]


def read(path):
    with open(path, "r", encoding="utf-8") as fh:
        return fh.read()


def write(path, content):
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8", newline="\n") as fh:
        fh.write(content)


def strip_php(text):
    text = re.sub(r"<\?(?:php)?(?:(?!\?>).)*\?>", "", text, flags=re.DOTALL)
    text = re.sub(r"<\?php[\s\S]*", "", text)
    return text


def php_links_to_html(text):
    for name in PHP_TO_HTML:
        text = text.replace(f"{name}.php", f"{name}.html")
    return text


def rewrite_assets(text):
    for src, dest in ASSET_REWRITES:
        # Prevent double-rewriting: only replace src when not already prefixed with ../ or ./
        pattern = r'(?<!\.\./)(?<!\./)' + re.escape(src)
        text = re.sub(pattern, dest, text)
    return text


def build_nav(page_html):
    sidebar = strip_php(read(os.path.join(FRONTEND, "partials", "sidebar.php")))
    header = strip_php(read(os.path.join(FRONTEND, "partials", "header.php")))
    header = header.replace("<?php echo htmlspecialchars($userName); ?>", "Admin User")
    header = header.replace("<?php echo htmlspecialchars($userRole); ?>", "Administrator")
    nav_rest = strip_php(read(os.path.join(FRONTEND, "partials", "nav.php")))
    nav_rest = re.sub(
        r"require_once __DIR__ \. '/sidebar\.php';\s*require_once __DIR__ \. '/header\.php';",
        "",
        nav_rest,
    )
    sidebar = php_links_to_html(sidebar)
    header = php_links_to_html(header)
    nav_rest = php_links_to_html(nav_rest)
    sidebar = re.sub(
        rf'(href="{re.escape(page_html)}" class="nav-item-link)\s*"',
        r'\1 active"',
        sidebar,
    )
    return sidebar + "\n" + header + "\n" + nav_rest


def convert_page(filename):
    content = read(os.path.join(FRONTEND, filename))
    page_html = filename.replace(".php", ".html")

    content = content.replace(
        'value="<?php echo $student_id; ?>"',
        'value=""',
    )
    content = content.replace(
        "const studentId = <?php echo $student_id; ?>;",
        'const studentId = Number(new URLSearchParams(window.location.search).get("id")) || 1;\n'
        "    const docStudentIdEl = document.getElementById(\"docStudentId\");\n"
        "    if (docStudentIdEl) docStudentIdEl.value = String(studentId);",
    )
    content = re.sub(
        r"<\?php echo htmlspecialchars\(\$_SESSION\['user'\]\['name'\] \?\? '.*?'\); \?>",
        "SPVM3 Tech Solution by Sanjay G L",
        content,
    )
    content = re.sub(
        r"<\?php echo htmlspecialchars\(\$_SESSION\['user'\]\['email'\] \?\? '.*?'\); \?>",
        "admin@university.edu",
        content,
    )
    content = re.sub(r"<script>\s*window\.API_BASE =.*?</script>", "", content, flags=re.DOTALL)
    content = re.sub(r"window\.API_BASE = '<\?php.*?\?>';", "", content)
    content = re.sub(r"window\.API_TOKEN = '<\?php.*?\?>';", "", content)

    if '<link rel="icon"' not in content:
        content = content.replace(
            "<title>",
            '<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">\n  <link rel="alternate icon" href="../assets/favicon.ico">\n  <title>',
        )

    content = content.replace("<?php include 'partials/nav.php'; ?>", build_nav(page_html))
    content = strip_php(content)
    content = php_links_to_html(content)
    content = rewrite_assets(content)
    content = re.sub(r"\n{3,}", "\n\n", content)
    content = content.replace(
        "Job drive posted successfully to PostgreSQL database.",
        "Job drive posted successfully.",
    )

    if filename == "login.php":
        login_script = """document.getElementById('loginForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const email = document.getElementById('email').value || 'admin@college.edu';
      localStorage.setItem('token', 'demo_admin_token');
      localStorage.setItem('user', JSON.stringify({ name: 'SPVM3 Tech Solution by Sanjay G L', role: 'admin', email: email }));
      window.location.href = 'dashboard.html';
    });"""
        content = re.sub(
            r"document\.getElementById\('loginForm'\)\.addEventListener\('submit',[\s\S]*?\n    \}\);",
            login_script,
            content,
        )

    return content


def write_index():
    write(
        os.path.join(OUTPUT, "index.html"),
        """<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="0; url=dashboard.html">
  <title>Placement Pro — SPVM3 Tech Solution</title>
  <script>window.location.href = "dashboard.html";</script>
</head>
<body>
  <p>Redirecting to <a href="dashboard.html">Placement Pro Dashboard</a>...</p>
</body>
</html>
""",
    )


def write_logout():
    write(
        os.path.join(OUTPUT, "logout.html"),
        """<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="refresh" content="0; url=login.html">
  <title>Logging out... — Placement Pro</title>
  <script>
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.location.href = "login.html";
  </script>
</head>
<body>
  <p>Logging out... <a href="login.html">Click here if not redirected</a>.</p>
</body>
</html>
""",
    )


def sync_assets():
    frontend_assets = os.path.join(FRONTEND, "assets")
    root_assets = os.path.join(ROOT, "assets")
    output_assets = os.path.join(OUTPUT, "assets")
    if os.path.exists(frontend_assets):
        shutil.copytree(frontend_assets, root_assets, dirs_exist_ok=True)
        shutil.copytree(frontend_assets, output_assets, dirs_exist_ok=True)

    fav_ico = os.path.join(FRONTEND, "favicon.ico")
    fav_svg = os.path.join(FRONTEND, "favicon.svg")
    targets = [ROOT, FRONTEND, OUTPUT, root_assets, output_assets, os.path.join(FRONTEND, "assets")]
    for t in targets:
        os.makedirs(t, exist_ok=True)
        for src in [fav_ico, fav_svg]:
            if os.path.exists(src):
                dest = os.path.join(t, os.path.basename(src))
                try:
                    shutil.copy2(src, dest)
                except PermissionError:
                    pass
    print("Synchronized all asset and favicon directories.")


def main():
    os.makedirs(OUTPUT, exist_ok=True)
    for filename in PAGES:
        path = os.path.join(FRONTEND, filename)
        if not os.path.exists(path):
            print(f"Skip missing {filename}")
            continue
        write(os.path.join(OUTPUT, filename.replace(".php", ".html")), convert_page(filename))
        print(f"Converted {filename} -> {filename.replace('.php', '.html')}")
    write_index()
    write_logout()
    sync_assets()
    print("Rendered static HTML previews.")


if __name__ == "__main__":
    main()
