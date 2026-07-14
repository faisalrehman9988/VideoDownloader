# Universal Video Downloader

Paste a Facebook, YouTube, Instagram, or TikTok link → the site downloads the video and gives you a preview + download button.

## How it works
1. User pastes a URL on `index.php` (auto-detects platform) or directly on `facebook.php` / `youtube.php` / `instagram.php` / `tiktok.php`.
2. `downloader.js` sends the URL to `process_video.php` via fetch/POST.
3. `process_video.php` runs **yt-dlp** (Python) to download the video. For TikTok it downloads as `.mkv` then converts to `.mp4` with **ffmpeg**.
4. File is saved in `downloads/`, and the JSON response gives back a video preview URL + a download link.
5. `download.php` streams the final file to the user as an attachment.

## Tools & Libraries
- **PHP** – backend logic (no framework)
- **yt-dlp** (Python) – actual video extraction/downloading
- **ffmpeg** – merging/converting video+audio (needed for TikTok)
- **Tailwind CSS** (CDN) – styling
- **Vanilla JavaScript** – frontend logic (`downloader.js`), no framework
- **Google Fonts (Inter)**

## Requirements
- PHP server
- Python 3 + `yt-dlp` installed (`pip install yt-dlp`)
- ffmpeg installed and on PATH
- Write access to a `downloads/` folder

## Notes / To-Do
- Python & ffmpeg paths in `process_video.php` are currently hardcoded for one Windows machine — move to config if deploying elsewhere.
- User URL is passed into `shell_exec()` without `escapeshellarg()` — should be escaped for safety.
- No auto-cleanup of old files in `downloads/` — add a cron job to delete old videos periodically.
- `tiktok.PHP` has an uppercase extension — confirm your server resolves it correctly since Linux is case-sensitive.
