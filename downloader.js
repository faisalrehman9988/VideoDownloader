

(function () {
  const platform = window.APP_PLATFORM;

  const config = {
    facebook: {
      label: "Facebook",
      placeholder: "Paste a Facebook video link",
      example: "Example: https://www.facebook.com/watch/?v=123456789",
      domains: ["facebook.com", "fb.watch"]
    },
    youtube: {
      label: "YouTube",
      placeholder: "Paste a YouTube video link",
      example: "Example: https://www.youtube.com/watch?v=dQw4w9WgXcQ",
      domains: ["youtube.com", "youtu.be"]
    },
    instagram: {
      label: "Instagram",
      placeholder: "Paste an Instagram reel or post link",
      example: "Example: https://www.instagram.com/reel/xxxxxxxxxxx/",
      domains: ["instagram.com"]
    },
    tiktok: {
      label: "TikTok",
      placeholder: "Paste a TikTok video link",
      example: "Example: https://www.tiktok.com/@user/video/1234567890",
      domains: ["tiktok.com"]
    }
  };

  const cfg = config[platform];
  if (!cfg) {
    console.error("Unknown platform:", platform);
    return;
  }

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("downloaderForm");
    const urlInput = document.getElementById("url");
    const submitBtn = document.getElementById("submitBtn");
    const alertBox = document.getElementById("alertBox");
    const spinnerWrap = document.getElementById("spinnerWrap");
    const videoSection = document.getElementById("videoSection");
    const videoPlayer = document.getElementById("videoPlayer");
    const videoSource = document.getElementById("videoSource");
    const videoName = document.getElementById("videoName");
    const videoSize = document.getElementById("videoSize");
    const downloadLink = document.getElementById("downloadLink");
    const copyToast = document.getElementById("copyToast");
    const mismatchNote = document.getElementById("mismatchNote");
    const helperText = document.getElementById("helperText");

    let currentDownloadUrl = "";

    urlInput.placeholder = cfg.placeholder;
    if (helperText) helperText.textContent = cfg.example;

    
    const params = new URLSearchParams(window.location.search);
    const prefillUrl = params.get("url");
    if (prefillUrl) {
      urlInput.value = prefillUrl;
      if (params.get("auto") === "1") {
        setTimeout(() => form.requestSubmit(), 50);
      }
    }

   
    urlInput.addEventListener("input", () => {
      const val = urlInput.value.trim().toLowerCase();
      if (!val) {
        mismatchNote.classList.add("hidden");
        return;
      }
      const belongsHere = cfg.domains.some((d) => val.includes(d));
      let looksLikeOther = false;
      for (const [key, other] of Object.entries(config)) {
        if (key === platform) continue;
        if (other.domains.some((d) => val.includes(d))) {
          looksLikeOther = other.label;
          break;
        }
      }
      if (!belongsHere && looksLikeOther) {
        mismatchNote.textContent =
          "This looks like a " + looksLikeOther + " link, not " + cfg.label + ".";
        mismatchNote.classList.remove("hidden");
      } else {
        mismatchNote.classList.add("hidden");
      }
    });

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      let url = urlInput.value.trim();
      if (!url) {
        showAlert("Please enter a video URL", "error");
        return;
      }
      if (!/^https?:\/\//i.test(url)) {
        url = "https://" + url;
      }

      alertBox.classList.add("hidden");
      videoSection.classList.add("hidden");
      spinnerWrap.classList.remove("hidden");
      submitBtn.disabled = true;
      submitBtn.classList.add("opacity-60", "cursor-not-allowed");

      try {
       const response = await fetch("process_video.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ url: url, platform: platform })
        });

        const data = await response.json();

        spinnerWrap.classList.add("hidden");
        submitBtn.disabled = false;
        submitBtn.classList.remove("opacity-60", "cursor-not-allowed");

        if (data.success) {
          currentDownloadUrl = data.download_url;
          videoSource.src = data.video_url;
          videoPlayer.load();
          videoName.textContent = data.filename;
          videoSize.textContent = data.filesize;
          downloadLink.href = data.download_url;
          videoSection.classList.remove("hidden");
          showAlert("Video downloaded and ready!", "success");
        } else {
          showAlert("Error: " + data.message, "error");
        }
      } catch (err) {
        spinnerWrap.classList.add("hidden");
        submitBtn.disabled = false;
        submitBtn.classList.remove("opacity-60", "cursor-not-allowed");
        showAlert("Error: " + err.message, "error");
        console.error(err);
      }
    });

    window.copyVideoUrl = function () {
      if (!currentDownloadUrl) return;
      navigator.clipboard
        .writeText(window.location.origin + currentDownloadUrl)
        .then(() => {
          copyToast.classList.remove("hidden");
          setTimeout(() => copyToast.classList.add("hidden"), 2000);
        });
    };

    function showAlert(message, type) {
      alertBox.textContent = message;
      alertBox.classList.remove("hidden", "bg-emerald-50", "text-emerald-700", "bg-red-50", "text-red-700", "border-emerald-200", "border-red-200");
      if (type === "success") {
        alertBox.classList.add("bg-emerald-50", "text-emerald-700", "border-emerald-200");
      } else {
        alertBox.classList.add("bg-red-50", "text-red-700", "border-red-200");
      }
    }
  });
})();