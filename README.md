# e-hepa — Sistem Pengurusan HEPA

`e-hepa` ialah sistem pengurusan dalaman bagi menyokong operasi dan tadbir urus **HEPA (Hal Ehwal Pelajar & Alumni)**.  
Sistem ini dibangunkan berasaskan **e-base Secure System Template**, dengan fokus kepada keselamatan, kebolehkembangan, dan pematuhan kawalan akses.

---

## 🎯 Tujuan Sistem

Sistem ini bertujuan untuk:
- Menyokong pengurusan modul HEPA secara berpusat
- Menyediakan kawalan akses berasaskan peranan (RBAC)
- Memastikan semua tindakan kritikal direkod melalui audit log
- Membolehkan penambahan modul baharu tanpa menjejaskan teras sistem

---

## 🧱 Seni Bina & Prinsip

### 🔒 Teras Sistem (Core)
Bahagian teras sistem adalah **dikunci** dan **tidak dibenarkan diubah** tanpa semakan teknikal:

- Pengesanan persekitaran & kawalan pendedahan ralat
- Pengurusan sesi & autentikasi
- Penguatkuasaan peranan dan kebenaran
- Mekanisme audit log
- Kawalan akses admin

Teras ini diwarisi terus daripada **e-base template** untuk memastikan konsistensi dan keselamatan jangka panjang.

---

### 🧩 Extension-Based Design
Semua fungsi HEPA dibangunkan sebagai **extension / modul** berasingan.

Ciri utama:
- Tiada perubahan terus pada core
- Modul boleh diaktifkan / dinyahaktifkan
- Setiap modul boleh ada peranan & audit sendiri

---

## ✨ Ciri Utama

- Sistem bootstrap yang selamat
- Role-based access control (RBAC)
- Backend permission guard
- Audit logging untuk tindakan kritikal
- Struktur projek bersedia untuk pengembangan modul
- Sokongan panel admin (terhad & terkawal)

---

## 🗂️ Struktur Projek (Ringkas)

```text
/app
  /core              # Teras sistem (DO NOT MODIFY)
  /extensions        # Modul HEPA & extension lain
  /configuration     # Konfigurasi sistem
  /helpers           # Helper bersama
