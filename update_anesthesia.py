import base64
import os
import re

base_dir = r"c:\xampp\htdocs\dentconsent\backend"
anesthesia_file = os.path.join(base_dir, "saveetha_consent_anesthesia.html")
circulear_img = os.path.join(base_dir, "circulear.png")
tiangle_img = os.path.join(base_dir, "tiangle.png")

with open(circulear_img, "rb") as f:
    circulear_b64 = "data:image/png;base64," + base64.b64encode(f.read()).decode('utf-8')

with open(tiangle_img, "rb") as f:
    tiangle_b64 = "data:image/png;base64," + base64.b64encode(f.read()).decode('utf-8')

with open(anesthesia_file, "r", encoding="utf-8") as f:
    html = f.read()

# Replace the Logo Upload Section HTML
logo_upload_section_regex = r"<!-- ══ LOGO UPLOAD SECTION ══════════════════════════════ -->(.*?)<!-- ════════════════════════════════════════════════════ -->"
html = re.sub(logo_upload_section_regex, "", html, flags=re.DOTALL)

# Replace the logo state JS
logo_js_regex = r"// ── Logo state ──────────────────────────────────────────\s*const logoSrc = \{ left: null, right: null \};\s*function loadLogo.*?function logoTag\(side\) \{.*?return '<div class=\"pdf-logo-placeholder\">' \+ label \+ '</div>';\s*\}"
new_logo_js = f"""// ── Logo state ──────────────────────────────────────────
const logoSrc = {{ left: "{tiangle_b64}", right: "{circulear_b64}" }};

function logoTag(side) {{
  return '<img src="' + logoSrc[side] + '" alt="' + side + ' logo">';
}}"""
html = re.sub(logo_js_regex, new_logo_js, html, flags=re.DOTALL)

# Add auto-process logic
auto_script = """
// ── Auto Fill & Download ─────────────────────────────────
function autoProcess() {
  const params = new URLSearchParams(window.location.search);
  if (params.has('auto')) {
    if (params.has('name')) document.getElementById("f-name").value = params.get('name');
    if (params.has('date')) document.getElementById("f-date").value = params.get('date');
    if (params.has('age')) document.getElementById("f-age").value = params.get('age');
    if (params.has('gender')) document.getElementById("f-gender").value = params.get('gender');
    if (params.has('sig')) document.getElementById("f-sig").value = params.get('sig');
    if (params.has('sigdate')) document.getElementById("f-sigdate").value = params.get('sigdate');
    if (params.has('rel')) document.getElementById("f-rel").value = params.get('rel');
    
    update();
    setTimeout(() => { generatePDF(); }, 500);
  } else {
    update();
  }
}
autoProcess();
"""
html = html.replace("update();\n</script>", auto_script + "\n</script>")

with open(anesthesia_file, "w", encoding="utf-8") as f:
    f.write(html)

print("Done updating anesthesia file.")
