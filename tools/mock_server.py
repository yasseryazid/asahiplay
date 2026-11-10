"""Server tiruan yang meniru API Laravel Asahi, hanya untuk menguji frontend tanpa PHP/Composer."""
import json, re, sys, time, pathlib
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from http.cookies import SimpleCookie

ROOT = pathlib.Path(__file__).resolve().parent.parent
TPL = (ROOT / 'tools/app.test.html').read_text(encoding='utf-8')
BANKS = {}
for f in (ROOT / 'database/seeders/data').glob('*.json'):
    d = json.loads(f.read_text(encoding='utf-8')); BANKS[d['slug']] = d
IKSC_PUBLIC = '--iksc' in sys.argv
PUBLIC = [s for s, d in BANKS.items() if (IKSC_PUBLIC if s == 'IKSC' else d.get('public', True))]

DB = {'user': {'id': 1, 'name': 'Yasser', 'email': 'yasser@example.com', 'avatar': None, 'parent': None, 'msg': None, 'promise': None, 'sound': True, 'consented': False},
      'children': [], 'plays': [], 'seq': 1}
LOG = []

def best(cid):
    b = {}
    for p in DB['plays']:
        if p['child_id'] == cid:
            k = f"{p['lv']}:{p['tk']}"; b[k] = max(b.get(k, 0), p['stars'])
    return b

class H(BaseHTTPRequestHandler):
    def log_message(self, *a): pass
    def authed(self):
        c = SimpleCookie(self.headers.get('Cookie', '')); return 'sid' in c and c['sid'].value == '1'
    def send(self, code, body=b'', ctype='application/json', headers=None):
        if isinstance(body, (dict, list)): body = json.dumps(body).encode()
        elif isinstance(body, str): body = body.encode()
        self.send_response(code); self.send_header('Content-Type', ctype); self.send_header('Content-Length', str(len(body)))
        for k, v in (headers or {}).items(): self.send_header(k, v)
        self.end_headers(); self.wfile.write(body)
    def body(self):
        n = int(self.headers.get('Content-Length') or 0); return json.loads(self.rfile.read(n) or b'{}') if n else {}
    def handle_any(self, m):
        path = self.path.split('?')[0]; LOG.append((m, path))
        if m == 'GET' and path == '/':
            boot = {'server': True, 'csrf': 'test', 'user': DB['user'] if self.authed() else None,
                    'children': DB['children'] if self.authed() else [],
                    'best': {c['id']: best(c['id']) for c in DB['children']} if self.authed() else {},
                    'banks': PUBLIC, 'authError': None}
            return self.send(200, TPL.replace('__BOOT__', json.dumps(boot)), 'text/html; charset=utf-8')
        if path == '/auth/google': return self.send(302, '', headers={'Location': '/', 'Set-Cookie': 'sid=1; Path=/'})
        if path == '/logout' and m == 'POST': return self.send(302, '', headers={'Location': '/', 'Set-Cookie': 'sid=0; Path=/'})
        if path == '/privasi': return self.send(200, '<h1>Privasi</h1>', 'text/html')
        mm = re.fullmatch(r'/api/banks/(\w+)', path)
        if mm and m == 'GET':
            s = mm.group(1)
            if s not in PUBLIC: return self.send(404, {'message': 'Not found'})
            d = BANKS[s]; return self.send(200, {'slug': s, 'title': d['title'], 'ordered': d['ordered'], 'assets': d.get('assets') or {}, 'questions': d['questions']})
        mm = re.fullmatch(r'/banks/(\w+)/([\w\-]+\.jpg)', path)
        if mm:
            if mm.group(1) not in PUBLIC: return self.send(404)
            f = ROOT / 'storage/app/banks' / mm.group(1).lower() / mm.group(2)
            return self.send(200, f.read_bytes(), 'image/jpeg') if f.exists() else self.send(404)
        if path.startswith('/api/'):
            if not self.authed(): return self.send(401, {'message': 'Unauthenticated.'})
            if m != 'GET' and self.headers.get('X-CSRF-TOKEN') != 'test': return self.send(419, {'message': 'CSRF token mismatch.'})
            if path == '/api/profile' and m == 'PUT':
                b = self.body(); u = DB['user']
                for k in ('parent', 'msg', 'promise', 'sound'):
                    if k in b: u[k] = b[k]
                if b.get('consent'): u['consented'] = True
                return self.send(200, {'user': u})
            if path == '/api/children' and m == 'POST':
                b = self.body()
                if not b.get('name') or b.get('grade') not in ('tk', 'k1', 'k2', 'k3', 'k4', 'k5'): return self.send(422, {'message': 'Data anak tidak valid.'})
                DB['seq'] += 1; c = {'id': DB['seq'], 'name': b['name'], 'grade': b['grade']}; DB['children'].append(c); return self.send(201, {'child': c})
            mm = re.fullmatch(r'/api/children/(\d+)(/plays)?', path)
            if mm:
                cid = int(mm.group(1)); c = next((x for x in DB['children'] if x['id'] == cid), None)
                if not c: return self.send(404, {'message': 'Not found'})
                if mm.group(2):
                    if m == 'GET':
                        ps = [p for p in DB['plays'] if p['child_id'] == cid][::-1]
                        return self.send(200, {'plays': [{k: v for k, v in p.items() if k != 'child_id'} for p in ps], 'best': best(cid)})
                    b = self.body(); DB['seq'] += 1
                    p = {'id': DB['seq'], 'child_id': cid, 'd': int(time.time() * 1000), 'lv': b['lv'], 'tk': b['tk'], 'stars': b['stars'], 'score': b.get('score'), 'together': b.get('together', False), 'by': b.get('by') or [0, 0], 'stamp': None}
                    DB['plays'].append(p); return self.send(201, {'play': p})
                if m == 'PATCH': c.update({k: v for k, v in self.body().items() if k in ('name', 'grade')}); return self.send(200, {'child': c})
                if m == 'DELETE':
                    DB['children'].remove(c); DB['plays'] = [p for p in DB['plays'] if p['child_id'] != cid]; return self.send(204)
            mm = re.fullmatch(r'/api/plays/(\d+)', path)
            if mm and m == 'PATCH':
                p = next((x for x in DB['plays'] if x['id'] == int(mm.group(1))), None)
                if not p: return self.send(404)
                p['stamp'] = self.body().get('stamp'); return self.send(200, {'play': p})
        self.send(404, {'message': 'Not found'})
    def do_GET(self): self.handle_any('GET')
    def do_POST(self): self.handle_any('POST')
    def do_PUT(self): self.handle_any('PUT')
    def do_PATCH(self): self.handle_any('PATCH')
    def do_DELETE(self): self.handle_any('DELETE')

if __name__ == '__main__':
    port = int(next((a for a in sys.argv[1:] if a.isdigit()), 8765))
    ThreadingHTTPServer(('127.0.0.1', port), H).serve_forever()
