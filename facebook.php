<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Facebook Video Downloader</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .bg-decor { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
  .bg-decor::before, .bg-decor::after { content: ''; position: absolute; border-radius: 9999px; filter: blur(90px); opacity: 0.4; }
  .bg-decor::before { width: 460px; height: 460px; background: #6ee7b7; top: -160px; left: -140px; }
  .bg-decor::after { width: 420px; height: 420px; background: #93c5fd; bottom: -180px; right: -120px; }
  .bg-grid {
    position: fixed; inset: 0; z-index: -1;
    background-image: radial-gradient(circle, #e5e5e5 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(ellipse 60% 50% at 50% 0%, black 40%, transparent 100%);
  }
</style>
</head>
<body class="bg-neutral-50 text-neutral-900 min-h-screen flex flex-col relative">
<div class="bg-grid"></div>
<div class="bg-decor"></div>

<header class="sticky top-0 z-40 border-b border-neutral-200/70 bg-white/70 backdrop-blur-md">
    <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-sm shadow-emerald-200">
                <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5"><path d="M12 3v12m0 0l-4-4m4 4l4-4M5 19h14" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
            <span class="font-extrabold text-lg tracking-tight"><span class="text-emerald-500">Universal Downloader</span></span>
        </a>
        <nav class="hidden sm:flex items-center gap-6 text-sm">
                <a href="index.php" class="text-neutral-500 hover:text-neutral-900 transition-colors">Home</a>
                <a href="facebook.php" class="text-neutral-900 font-semibold transition-colors">Facebook</a>
                <a href="youtube.php" class="text-neutral-500 hover:text-neutral-900 transition-colors">YouTube</a>
                <a href="instagram.php" class="text-neutral-500 hover:text-neutral-900 transition-colors">Instagram</a>
                <a href="tiktok.php" class="text-neutral-500 hover:text-neutral-900 transition-colors">TikTok</a>
        </nav>
    </div>
</header>

<main class="flex-1">
    <section class="max-w-2xl mx-auto px-6 pt-16 pb-10 text-center">
        <div class="bg-white/80 backdrop-blur border border-neutral-200/70 rounded-2xl shadow-sm shadow-neutral-200/50 px-6 sm:px-10 py-10">
        <span class="inline-flex items-center gap-2 rounded-full bg-blue-600 text-white text-xs font-semibold px-3 py-1 mb-5">
            <svg viewBox="0 0 24 24" fill="white" class="w-6 h-6"><path d="M13.5 9H15V6.5h-1.5C11.6 6.5 10 8.1 10 10v2H8v3h2v6h3v-6h2.2l.8-3H13v-1.5c0-.55.45-1 1-1z"/></svg> Facebook
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-3">Facebook Video Downloader</h1>
        <p class="text-neutral-500 mb-8">Paste a Facebook link below to preview and download the video.</p>

        <form id="downloaderForm" class="flex flex-col sm:flex-row gap-3">
            <input
                type="text"
                id="url"
                name="url"
                class="flex-1 rounded-xl border border-neutral-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                placeholder=""
                required
            >
            <button type="submit" id="submitBtn" class="rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3 text-sm transition-colors">
                Download
            </button>
        </form>
        <p class="text-xs text-neutral-400 mt-2 text-left" id="helperText"></p>
        <p class="text-xs text-amber-600 mt-2 text-left hidden" id="mismatchNote"></p>

        <div class="hidden text-sm rounded-lg border px-4 py-3 mt-5 text-left" id="alertBox"></div>

        <div class="hidden flex-col items-center gap-3 py-8" id="spinnerWrap">
            <div class="h-8 w-8 rounded-full border-2 border-neutral-200 border-t-emerald-500 animate-spin"></div>
            <p class="text-sm text-neutral-500">Downloading video&hellip; this may take 10&ndash;30 seconds</p>
        </div>

        <div class="hidden text-left mt-6" id="videoSection">
            <div class="rounded-xl bg-neutral-50 border border-neutral-100 px-4 py-3 mb-4 text-sm">
                <p class="text-neutral-500">File: <strong class="text-neutral-900" id="videoName"></strong></p>
                <p class="text-neutral-500">Size: <strong class="text-neutral-900" id="videoSize"></strong></p>
            </div>
            <div class="rounded-xl overflow-hidden bg-black mb-4">
                <video id="videoPlayer" controls class="w-full">
                    <source id="videoSource" src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="#" id="downloadLink" class="flex-1 text-center rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-6 py-3 text-sm transition-colors">Download Video</a>
                <button type="button" onclick="copyVideoUrl()" class="flex-1 rounded-xl bg-neutral-900 hover:bg-neutral-700 text-white font-semibold px-6 py-3 text-sm transition-colors">Copy URL</button>
            </div>
        </div>
        </div>
    </section>

    <section class="max-w-3xl mx-auto px-6 pb-16">
        <p class="text-center text-xs font-semibold text-neutral-400 uppercase tracking-wide mb-4">Other platforms</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="facebook.php" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 hover:border-neutral-300 hover:shadow-sm transition-all"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600"><svg viewBox="0 0 24 24" fill="white" class="w-6 h-6"><path d="M13.5 9H15V6.5h-1.5C11.6 6.5 10 8.1 10 10v2H8v3h2v6h3v-6h2.2l.8-3H13v-1.5c0-.55.45-1 1-1z"/></svg></span><span><span class="block text-sm font-semibold text-neutral-900">Facebook</span><span class="block text-xs text-neutral-500">Videos & Reels</span></span></a>
            <a href="youtube.php" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 hover:border-neutral-300 hover:shadow-sm transition-all"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-600"><svg viewBox="0 0 24 24" fill="white" class="w-6 h-6"><path d="M21.6 7.2s-.2-1.5-.8-2.1c-.8-.8-1.7-.8-2.1-.9C15.9 4 12 4 12 4h0s-3.9 0-6.7.2c-.4 0-1.3.1-2.1.9-.6.6-.8 2.1-.8 2.1S2.2 9 2.2 10.7v1.6c0 1.8.2 3.5.2 3.5s.2 1.5.8 2.1c.8.8 1.8.8 2.3.9 1.7.1 7 .2 7 .2s3.9 0 6.7-.3c.4 0 1.3-.1 2.1-.9.6-.6.8-2.1.8-2.1s.2-1.8.2-3.5v-1.6c0-1.8-.2-3.5-.2-3.5zM9.9 14.6V8.9l5.4 2.9-5.4 2.8z"/></svg></span><span><span class="block text-sm font-semibold text-neutral-900">YouTube</span><span class="block text-xs text-neutral-500">Videos & Shorts</span></span></a>
            <a href="instagram.php" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 hover:border-neutral-300 hover:shadow-sm transition-all"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-pink-600"><svg viewBox="0 0 24 24" fill="white" class="w-6 h-6"><path d="M12 2.2c2.7 0 3 0 4.1.1 1 .1 1.6.2 2 .4.5.2.9.4 1.2.8.4.3.6.7.8 1.2.1.4.3 1 .4 2 .1 1.1.1 1.4.1 4.1s0 3-.1 4.1c-.1 1-.2 1.6-.4 2-.2.5-.4.9-.8 1.2-.3.4-.7.6-1.2.8-.4.1-1 .3-2 .4-1.1.1-1.4.1-4.1.1s-3 0-4.1-.1c-1-.1-1.6-.2-2-.4-.5-.2-.9-.4-1.2-.8-.4-.3-.6-.7-.8-1.2-.1-.4-.3-1-.4-2-.1-1.1-.1-1.4-.1-4.1s0-3 .1-4.1c.1-1 .2-1.6.4-2 .2-.5.4-.9.8-1.2.3-.4.7-.6 1.2-.8.4-.1 1-.3 2-.4C9 2.2 9.3 2.2 12 2.2zm0 1.8c-2.6 0-2.9 0-4 .1-.9.1-1.4.2-1.7.3-.4.2-.7.3-1 .6-.3.3-.4.6-.6 1-.1.3-.2.8-.3 1.7-.1 1.1-.1 1.4-.1 4s0 2.9.1 4c.1.9.2 1.4.3 1.7.2.4.3.7.6 1 .3.3.6.4 1 .6.3.1.8.2 1.7.3 1.1.1 1.4.1 4 .1s2.9 0 4-.1c.9-.1 1.4-.2 1.7-.3.4-.2.7-.3 1-.6.3-.3.4-.6.6-1 .1-.3.2-.8.3-1.7.1-1.1.1-1.4.1-4s0-2.9-.1-4c-.1-.9-.2-1.4-.3-1.7-.2-.4-.3-.7-.6-1-.3-.3-.6-.4-1-.6-.3-.1-.8-.2-1.7-.3-1.1-.1-1.4-.1-4-.1zm0 3.5a4.5 4.5 0 110 9 4.5 4.5 0 010-9zm0 1.8a2.7 2.7 0 100 5.4 2.7 2.7 0 000-5.4zm5.7-2a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0z"/></svg></span><span><span class="block text-sm font-semibold text-neutral-900">Instagram</span><span class="block text-xs text-neutral-500">Reels & Posts</span></span></a>
            <a href="tiktok.php" class="flex items-center gap-3 rounded-xl border border-neutral-200 px-4 py-3 hover:border-neutral-300 hover:shadow-sm transition-all"><span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-neutral-900"><svg viewBox="0 0 24 24" fill="white" class="w-6 h-6"><path d="M16.6 5.2c-.8-.7-1.3-1.7-1.4-2.8h-2.9v13.2c0 1.3-1.1 2.4-2.4 2.4a2.4 2.4 0 01-2.4-2.4 2.4 2.4 0 012.4-2.4c.3 0 .5 0 .8.1V10.3c-.3 0-.5-.1-.8-.1-3 0-5.4 2.4-5.4 5.4S7.9 21 10.9 21s5.4-2.4 5.4-5.4V8.9c1.1.8 2.5 1.3 4 1.3V7.3c-1.3 0-2.5-.5-3.3-1.3-.1-.1-.3-.2-.4-.4v-.4z"/></svg></span><span><span class="block text-sm font-semibold text-neutral-900">TikTok</span><span class="block text-xs text-neutral-500">Videos</span></span></a>
        </div>
    </section>
</main>

<footer class="border-t border-neutral-100 py-6 text-center text-xs text-neutral-400">
    Only download videos you have the right to use or share.
</footer>

<div id="copyToast" class="hidden fixed bottom-5 right-5 bg-emerald-500 text-white text-sm font-medium px-4 py-3 rounded-xl shadow-lg">
    URL copied to clipboard!
</div>

<script>
  window.APP_PLATFORM = "facebook";
</script>
<script src="downloader.js"></script>
</body>
</html>