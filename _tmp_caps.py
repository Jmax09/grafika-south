import os, subprocess, time, urllib.request, json, http.cookiejar, re, sys
import urllib.parse

ROOT = r'C:\xampp\htdocs\grafika-south'
CHROME = r'C:\Program Files\Google\Chrome\Application\chrome.exe'
OUT = os.path.join(ROOT, 'docs', 'screenshots')
BASE = 'http://127.0.0.1:8123'


def alive():
    try:
        urllib.request.urlopen(BASE + '/', timeout=6)
        return True
    except Exception:
        return False


if not alive():
    subprocess.Popen(['php', 'artisan', 'serve', '--host=127.0.0.1', '--port=8123'],
                     cwd=ROOT, creationflags=0x08000000,
                     stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    for _ in range(40):
        time.sleep(1)
        if alive():
            break
print('server alive:', alive(), flush=True)


def shoot(name, url, w=1440, h=1600):
    out = os.path.join(OUT, name + '.png')
    if os.path.exists(out):
        os.remove(out)
    prof = os.path.join(os.environ['TEMP'], 'crx_' + name)
    args = [CHROME, '--headless=new', '--disable-gpu', '--hide-scrollbars',
            '--no-first-run', '--no-default-browser-check',
            '--user-data-dir=' + prof, '--virtual-time-budget=9000',
            '--window-size=%d,%d' % (w, h), '--screenshot=' + out, url]
    subprocess.Popen(args, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL,
                     stdin=subprocess.DEVNULL)
    waited = 0
    while not os.path.exists(out) and waited < 30000:
        time.sleep(0.4)
        waited += 400
    time.sleep(0.4)
    print(('OK   %-26s %8d bytes' % (name, os.path.getsize(out)))
          if os.path.exists(out) else ('FAIL %s' % name), flush=True)


def login(email, password, portal='customer'):
    jar = http.cookiejar.CookieJar()
    op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    html = op.open(BASE + '/login', timeout=30).read().decode('utf-8', 'replace')
    m = re.search(r'name="_token"\s+(?:value|content)="([^"]+)"', html)
    if not m:
        m = re.search(r'csrf-token"\s+content="([^"]+)"', html)
    tok = m.group(1) if m else ''
    data = urllib.parse.urlencode({
        'login': email, 'password': password, '_token': tok, 'login_portal': portal
    }).encode()
    req = urllib.request.Request(BASE + '/login', data=data)
    req.add_header('Content-Type', 'application/x-www-form-urlencoded')
    body = op.open(req, timeout=30).read().decode('utf-8', 'replace')
    signed_in = 'login_password' not in body
    if not signed_in:
        err = re.search(r'(?:error|invalid)[^<]{0,120}', body, re.I)
        print('login %s (%s) -> FAILED %s' % (email, portal, (err.group(0)[:90] if err else '')), flush=True)
    else:
        print('login %s (%s) -> OK' % (email, portal), flush=True)
    return op


if __name__ == '__main__':
    mode = sys.argv[1] if len(sys.argv) > 1 else 'public'
    if mode == 'public':
        shoot('landing-dark', BASE + '/__theme_seed.html?t=dark&p=/', 1440, 3000)
        shoot('landing-light', BASE + '/__theme_seed.html?t=light&p=/', 1440, 3000)
    else:
        staff = login('grafikasouth69@gmail.com', 'ThemeVerify!2026', 'admin')
        for name, path in [('admin-dashboard-dark', '/admin/dashboard'),
                           ('superadmin-dashboard-dark', '/super-admin/dashboard')]:
            html = staff.open(BASE + path, timeout=40).read().decode('utf-8', 'replace')
            dest = os.path.join(ROOT, 'public', '_preview', name + '.html')
            os.makedirs(os.path.dirname(dest), exist_ok=True)
            with open(dest, 'w', encoding='utf-8') as f:
                f.write(html)
            print('saved %s (%d bytes)' % (path, len(html)), flush=True)
            shoot(name, BASE + '/__theme_seed.html?t=dark&p=/_preview/' + name + '.html', 1440, 1600)

        cust = login('lalayankokak@gmail.com', 'ThemeVerify!2026', 'customer')
        html = cust.open(BASE + '/customer/dashboard', timeout=40).read().decode('utf-8', 'replace')
        dest = os.path.join(ROOT, 'public', '_preview', 'customer-dashboard-dark.html')
        with open(dest, 'w', encoding='utf-8') as f:
            f.write(html)
        print('saved /customer/dashboard (%d bytes)' % len(html), flush=True)
        shoot('customer-dashboard-dark',
              BASE + '/__theme_seed.html?t=dark&p=/_preview/customer-dashboard-dark.html', 1440, 1600)
