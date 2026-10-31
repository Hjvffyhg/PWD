# BAPS e-Library – Setup Guide (XAMPP + VS Code)

## A. Install / ihanda muna
1. I-install ang **XAMPP** (https://www.apachefriends.org) – PHP 8.x version.
2. I-install ang **Visual Studio Code**.
3. **Internet is needed** habang ginagamit (Font Awesome icons and Google Fonts are loaded online).

## B. Ilagay ang project sa XAMPP
1. Buksan ang `C:\xampp\htdocs\`.
2. I-copy dito ang buong folder **WDP-FINALPROJECT-BAPSeLibrary**.
   Dapat ganito: `C:\xampp\htdocs\WDP-FINALPROJECT-BAPSeLibrary\index.php`

## C. I-start ang XAMPP
1. Buksan ang **XAMPP Control Panel**.
2. Click **Start** sa **Apache** at **MySQL** (dapat green na pareho).

## D. I-import ang database
1. Sa browser, pumunta sa **http://localhost/phpmyadmin**.
2. Click **Import** (taas) → **Choose File** → piliin ang `database/baps_elibrary.sql` → **Import** (baba).
3. Makikita mo na ang database na **baps_elibrary** sa kaliwa (6 tables).
   (Kung ayaw gumana ang Import: click **SQL** tab, i-paste ang laman ng `baps_elibrary.sql`, tapos **Go**.)

## E. Buksan sa VS Code
1. VS Code → **File → Open Folder** → piliin ang `C:\xampp\htdocs\WDP-FINALPROJECT-BAPSeLibrary`.
2. Dito mo ine-edit ang code. I-save (Ctrl+S) tapos i-refresh ang browser (F5).

## F. Patakbuhin ang website
Sa browser: **http://localhost/WDP-FINALPROJECT-BAPSeLibrary/**

### Librarian accounts (default, 4 sila – automatic na nagagawa sa first load)
| Username | Password |
|---|---|
| barlizonico | nicobarlizo1 |
| almariojustine2 | justinealmario2 |
| pajacbyron | byronpajac3 |
| saligumbamiguel | miguelsaligumba4 |

### Student account
Click **SIGN UP**, mag-register (Student ID format: `2026-00001`), tapos **LOGIN**.

## G. Subukan ang buong flow (para sa demo)
1. Login as librarian → **Inventory** → **Add Book** (title, author, ISBN, category, copies, cover).
2. Logout → Register as student → Login.
3. **Explore** → click a book (preview, rating, pages, times borrowed) → **Borrow Now**.
4. Logout → Login as librarian → **Borrowed/Return Request** → **Approve**.
5. Student → **My Borrowed Books** → **Return** → librarian **Mark Returned** → student rates the book in **History**.

## H. Troubleshooting
- *"Database connection failed"* → naka-start ba ang MySQL? Na-import ba ang SQL? (check `config/db.php`)
- *Blank page / 404* → tama ba ang folder name sa `htdocs`?
- *Hindi nag-a-upload ng picture* → JPG/PNG/WEBP lang, max 2MB.
- *Port 80 busy (Apache won't start)* → isara ang Skype/IIS, o palitan ang port sa XAMPP Config.

## Folder structure
```
WDP-FINALPROJECT-BAPSeLibrary/
├─ index.php, login.php, register.php, logout.php   (public pages)
├─ settings.php, about.php, contact.php             (shared pages)
├─ student/    dashboard, explore, my_books, history, favorites, borrow, favorite
├─ librarian/  dashboard, inventory, students, requests, report
├─ includes/   functions.php, layout.php, public_parts.php
├─ config/     db.php
├─ assets/     css/, js/, images/
├─ uploads/    covers/, profiles/
└─ database/   baps_elibrary.sql
```
