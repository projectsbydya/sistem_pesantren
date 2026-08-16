# Testing Subdomain di Local Development

## 🎯 Quick Start (3 Langkah)

### 1. Edit Hosts File

**Windows (Run as Administrator):**
```powershell
notepad C:\Windows\System32\drivers\etc\hosts
```

**Tambahkan:**
```
127.0.0.1   localhost
127.0.0.1   demo1.localhost
127.0.0.1   demo2.localhost
```

### 2. Flush DNS
```powershell
ipconfig /flushdns
```

### 3. Jalankan Laravel
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### 4. Test di Browser
```
http://demo1.localhost:8000
http://demo2.localhost:8000
```

---

## 🔧 Metode Testing Subdomain

### Metode A: Hosts File (Recommended)

**Pros:**
- ✅ Gratis
- ✅ No internet required
- ✅ Full control

**Cons:**
- ❌ Manual setup per device
- ❌ Tidak bisa share ke tim

**Setup:**
```
# hosts file
127.0.0.1   tenant1.localhost
127.0.0.1   tenant2.localhost
127.0.0.1   tenant3.localhost
```

### Metode B: Laragon (Windows)

**Install:** [laragon.org](https://laragon.org)

**Auto-Virtual Hosts:**
```
K:\Projects\sistem_pesantren
    └── http://sistem-pesantren.test

K:\Projects\sistem_pesantren (subdomain mode)
    └── http://<subdomain>.sistem-pesantren.test
```

**Setup:**
1. Install Laragon
2. Set document root ke `K:\Projects\sistem_pesantren\public`
3. Aktifkan "Auto virtual hosts"
4. Akses: `http://demo1.sistem-pesantren.test`

### Metode C: Valet (Mac/Linux)

```bash
# Install Valet
composer global require laravel/valet
valet install

# Link project
cd K:\Projects\sistem_pesantren
valet link sistem-pesantren

# Access
http://sistem-pesantren.test
http://demo1.sistem-pesantren.test
```

### Metode D: ngrok (Public Testing)

```bash
# Install ngrok
choco install ngrok  # Windows
brew install ngrok    # Mac

# Authenticate
ngrok authtoken <your-token>

# Start tunnel
ngrok http 8000

# Hasil (random subdomain):
# https://abc123.ngrok.io
```

**Custom Subdomain (paid):**
```bash
ngrok http --subdomain=demo1 8000
# https://demo1.ngrok.io
```

### Metode E: Serveo.net (Free Public)

```bash
# SSH tunnel
ssh -R demo1:80:localhost:8000 serveo.net

# Akses:
# https://demo1.serveo.net
```

---

## 🧪 Testing Commands

### Test Subdomain Resolution
```bash
# Test dengan curl
curl -I http://demo1.localhost:8000
curl -I http://demo2.localhost:8000

# Test tenant resolution API
curl http://demo1.localhost:8000/test/tenant
curl -H "Accept: application/json" http://demo1.localhost:8000/api/santri
```

### Test DNS Resolution
```bash
# Windows
nslookup demo1.localhost
ping demo1.localhost

# Mac/Linux
dig demo1.localhost
host demo1.localhost
```

---

## 🐛 Troubleshooting

### Issue: "This site can't be reached"

**Check 1:** Hosts file entry
```powershell
# Check hosts file content
Get-Content C:\Windows\System32\drivers\etc\hosts
```

**Check 2:** Laravel running
```bash
php artisan serve --host=0.0.0.0
```

**Check 3:** Port access
```bash
# Test direct localhost
curl http://localhost:8000
```

### Issue: "Access denied" hosts file

**Windows - Run as Admin:**
```powershell
# Method 1: Right-click Notepad -> Run as Administrator
# Method 2: PowerShell Admin
Start-Process notepad -Verb runAs C:\Windows\System32\drivers\etc\hosts
```

**Mac/Linux:**
```bash
sudo nano /etc/hosts
```

### Issue: Subdomain resolves to real website

**Cek hosts file:**
```
# ❌ Salah: ada # di depan
# 127.0.0.1 demo1.localhost

# ✅ Benar: tanpa #
127.0.0.1 demo1.localhost
```

**Flush DNS lagi:**
```powershell
ipconfig /flushdns
```

### Issue: ngrok 502 Bad Gateway

**Check:** Laravel harus running di port yang benar
```bash
php artisan serve --port=8000
ngrok http 8000
```

---

## 📱 Testing di Mobile

### Metode 1: Same WiFi + IP Address

```bash
# Cek IP
ipconfig  # Windows (cari IPv4 Address)
ifconfig  # Mac/Linux

# Output: 192.168.1.100

# Edit hosts di mobile (rooted/jailbroken)
# Atau: Akses langsusng via IP
http://192.168.1.100:8000
```

### Metode 2: ngrok

```bash
ngrok http 8000

# Buka URL ngrok di mobile
# https://abc123.ngrok.io
```

---

## 🔄 Automated Testing Subdomain

### PHPUnit Test
```php
public function test_subdomain_tenant_resolution()
{
    $tenant = Tenant::factory()->create(['slug' => 'demo1']);
    
    $response = $this->get(
        'http://demo1.localhost/test/tenant',
        ['Accept' => 'application/json']
    );
    
    $response->assertJson([
        'tenant_id' => $tenant->id,
    ]);
}
```

### Dusk Browser Test
```php
public function test_login_via_subdomain()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('http://demo1.localhost:8000/login')
                ->type('email', 'admin@demo1.sch.id')
                ->type('password', 'your-test-password')
                ->press('Login')
                ->assertPathIs('/dashboard');
    });
}
```

---

## 📝 Summary Per Metode

| Metode | Setup | Shareable | Mobile | Best For |
|--------|-------|-----------|--------|----------|
| Hosts File | Medium | ❌ | ❌ | Solo dev |
| Laragon | Easy | ❌ | ❌ | Windows dev |
| Valet | Easy | ❌ | ❌ | Mac dev |
| ngrok | Easy | ✅ | ✅ | Demo/Share |
| Serveo | Easy | ✅ | ✅ | Quick share |

---

## 🎓 Best Practice

1. **Development:** Gunakan hosts file atau Laragon
2. **Staging:** Gunakan ngrok dengan custom subdomain
3. **Testing:** Gunakan PHPUnit dengan HTTP_HOST manipulation
4. **Production:** Setup wildcard DNS *.yourdomain.com

---

## 🔗 Wildcard DNS Production Setup

### Cloudflare Example
```
Type: A
Name: *.yourdomain.com
Value: Your.Server.IP.Address
TTL: Auto
```

### Nginx Config
```nginx
server {
    server_name ~^(?<subdomain>.+)\.yourdomain\.com$;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Subdomain $subdomain;
    }
}
```

### Laravel Tenant Resolution
```php
// Dari subdomain demo1.yourdomain.com
$subdomain = $request->route('subdomain'); // = 'demo1'
$tenant = Tenant::where('slug', $subdomain)->first();
```
