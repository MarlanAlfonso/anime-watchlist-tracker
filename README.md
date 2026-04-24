# 🎌 AniTrack - Anime Watchlist Tracker

AniTrack is a web-based application designed for anime enthusiasts to discover, track, and manage their anime watchlists. Users can heart their favorite shows, rate them, and see what is trending in the community.

## Features
- **User Authentication:** Secure Login and Registration system.
- **Anime Management:** Add, Edit, and Delete anime entries (CRUD).
- **Interactive Highlighting:** View Top Hearted, Top Rated, and Most Added anime in an interactive accordion.
- **Filtering & Search:** Filter by Status (Ongoing, Completed, Upcoming), Genre, or search by title.
- **Rating & Hearting:** Logged-in users can rate anime (1-10 stars) and show love by "hearting" entries.
- **Responsive Design:** Fully responsive dark-themed UI built with Bootstrap.
- **Image Uploads:** Support for anime cover images.

## Tech Stack
- **Backend:** PHP 8.x
- **Database:** MySQL
- **Frontend:** HTML5, CSS3 (Custom Dark Theme), JavaScript (jQuery)
- **Framework:** Bootstrap 3.4
- **Icons:** Glyphicons

## Setup & Installation
1. **Clone the repository**
```bash
   git clone https://github.com/MarlanAlfonso/Anime-Watchlist-Tracker.git
```

2. **Set up the database**
   - Create a MySQL database
   - Import `anime_db.sql`

3. **Configure database connection**
```bash
   cp db.example.php db.php
```
   Then edit `db.php` with your credentials:
```php
   define('DB_HOST', 'your_host');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
```

4. **Create upload folders** (if not present)
```bash
   mkdir uploads avatars
```

5. **Upload to your server** and visit the site!


## Database Tables

| Table | Description |
|---|---|
| `users` | Registered user accounts |
| `anime_watchlist` | Personal anime entries per user |
| `general_anime` | Public general anime list |
| `anime_hearts` | Hearts given to general anime |
| `anime_ratings` | Ratings given to general anime |

