<?php

header('Content-Type: application/json');

class VideoDownloader {
    private $outputDir = 'downloads/';
    private $pythonPath = '';
    private $supportedDomains = array(
        'facebook.com', 'fb.watch',
        'youtube.com', 'youtu.be',
        'instagram.com',
        'tiktok.com'
    );

    public function __construct() {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }

        $this->pythonPath = $this->findPythonPath();
    }

    private function findPythonPath() {
        $commonPaths = array(
            'C:\\Python314\\python.exe',
            'C:\\Python313\\python.exe',
            'C:\\Python312\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Program Files\\Python314\\python.exe',
            'C:\\Program Files\\Python313\\python.exe',
            'C:\\Program Files (x86)\\Python314\\python.exe',
        );

        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        $output = shell_exec('where python 2>&1');
        if ($output && strpos($output, 'python.exe') !== false) {
            $paths = explode("\n", trim($output));
            foreach ($paths as $path) {
                $path = trim($path);
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return 'python';
    }

    private function isSupportedUrl($url) {
        $url_lower = strtolower($url);
        foreach ($this->supportedDomains as $domain) {
            if (strpos($url_lower, $domain) !== false) {
                return true;
            }
        }
        return false;
    }

    private function detectPlatform($url) {
        $url_lower = strtolower($url);
        if (strpos($url_lower, 'facebook.com') !== false || strpos($url_lower, 'fb.watch') !== false) {
            return 'Facebook';
        }
        if (strpos($url_lower, 'youtube.com') !== false || strpos($url_lower, 'youtu.be') !== false) {
            return 'YouTube';
        }
        if (strpos($url_lower, 'instagram.com') !== false) {
            return 'Instagram';
        }
        if (strpos($url_lower, 'tiktok.com') !== false) {
            return 'TikTok';
        }
        return 'Unknown';
    }

    private function buildCommand($url, $outputPath, $platform) {
        // Default format selection
        $formatFlags = '--format best';

        switch ($platform) {
            case 'YouTube':
                
                $formatFlags = '--format "bestvideo[height<=1080]+bestaudio/bestvideo+bestaudio/best"';
                break;
            case 'Instagram':
            case 'Facebook':
            default:
                
                $formatFlags = '--format best';
                break;
        }

        $cookiesFlag = '';
        $cookiesFile = __DIR__ . '/cookies_' . strtolower($platform) . '.txt';
        if (file_exists($cookiesFile)) {
            $cookiesFlag = sprintf('--cookies "%s"', $cookiesFile);
        }

        $ffmpegFlag = $this->getFfmpegLocationFlag();

        $command = sprintf(
            '"%s" -m yt_dlp %s %s %s --merge-output-format mp4 -o "%s" "%s" 2>&1',
            $this->pythonPath,
            $formatFlags,
            $cookiesFlag,
            $ffmpegFlag,
            $outputPath,
            $url
        );

        return $command;
    }

    
    private function buildTikTokDownloadCommand($url, $tempPath) {
        $cookiesFlag = '';
        $cookiesFile = __DIR__ . '/cookies_tiktok.txt';
        if (file_exists($cookiesFile)) {
            $cookiesFlag = sprintf('--cookies "%s"', $cookiesFile);
        }

        $ffmpegFlag = $this->getFfmpegLocationFlag();

        return sprintf(
            '"%s" -m yt_dlp --format "bestvideo+bestaudio/best" %s %s --merge-output-format mkv -o "%s" "%s" 2>&1',
            $this->pythonPath,
            $cookiesFlag,
            $ffmpegFlag,
            $tempPath,
            $url
        );
    }

  
    private function buildFfmpegTranscodeCommand($inputPath, $outputPath) {
        $ffmpegExe = 'ffmpeg';
        $ffmpegLocation = $this->getFfmpegLocation();
        if ($ffmpegLocation && is_dir($ffmpegLocation)) {
            $ffmpegExe = rtrim($ffmpegLocation, '\\/') . '\\ffmpeg.exe';
        }

        return sprintf(
            '"%s" -y -i "%s" -c:v libx264 -c:a aac -pix_fmt yuv420p -movflags +faststart "%s" 2>&1',
            $ffmpegExe,
            $inputPath,
            $outputPath
        );
    }

    private function getFfmpegLocation() {
        $ffmpegLocation = 'C:\\Users\\Bitech-Office\\AppData\\Local\\Microsoft\\WinGet\\Packages\\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\\ffmpeg-8.1.2-full_build\\bin';
        return is_dir($ffmpegLocation) ? $ffmpegLocation : null;
    }

    private function getFfmpegLocationFlag() {
        $location = $this->getFfmpegLocation();
        return $location ? sprintf('--ffmpeg-location "%s"', $location) : '';
    }

    public function downloadVideo($url) {
        try {
            if (empty($this->pythonPath)) {
                throw new Exception('Python not found. Install Python from python.org');
            }

            if (!$this->isSupportedUrl($url)) {
                throw new Exception('Unsupported URL. Supported platforms: Facebook, YouTube, Instagram, TikTok');
            }

            $platform = $this->detectPlatform($url);

            $baseName = 'video_' . time() . '_' . uniqid();
            $outputPath = $this->outputDir . $baseName . '.mp4';
            $outputPath = str_replace('/', '\\', $outputPath);

            if ($platform === 'TikTok') {
              
                $tempPath = $this->outputDir . $baseName . '_temp.mkv';
                $tempPath = str_replace('/', '\\', $tempPath);

                $downloadCommand = $this->buildTikTokDownloadCommand($url, $tempPath);
                $output = shell_exec($downloadCommand);

                if (!file_exists($tempPath)) {
                    throw new Exception('TikTok download failed | DEBUG: ' . trim((string)$output));
                }

                $tempSize = filesize($tempPath);
                if ($tempSize < 10000) {
                    @unlink($tempPath);
                    throw new Exception('Downloaded TikTok file is too small | DEBUG: ' . trim((string)$output));
                }

                $transcodeCommand = $this->buildFfmpegTranscodeCommand($tempPath, $outputPath);
                $transcodeOutput = shell_exec($transcodeCommand);

                @unlink($tempPath);

                if (!file_exists($outputPath)) {
                    throw new Exception('TikTok video transcode failed | DEBUG: ' . trim((string)$transcodeOutput));
                }

            } else {
                $command = $this->buildCommand($url, $outputPath, $platform);
                $output = shell_exec($command);

                if (!file_exists($outputPath)) {
                    $error = 'Download failed';

                    if (strpos($output, 'private') !== false) {
                        $error = 'Video is private or requires authentication';
                    } elseif (strpos($output, 'not found') !== false) {
                        $error = 'Video not found';
                    } elseif (strpos($output, 'No video found') !== false) {
                        $error = 'No video found at this URL';
                    } elseif (strpos($output, 'No module named') !== false) {
                        $error = 'yt-dlp module not found. Run: python -m pip install yt-dlp';
                    } elseif (stripos($output, 'sign in') !== false || stripos($output, 'confirm') !== false) {
                        $error = 'YouTube is blocking this request (bot-check). Try adding a cookies_youtube.txt file, or run: python -m pip install -U yt-dlp';
                    } elseif (strpos($output, 'login') !== false || strpos($output, 'Login') !== false) {
                        $error = 'This content requires login/authentication and cannot be downloaded. Try adding a cookies_' . strtolower($platform) . '.txt file.';
                    } elseif (strpos($output, 'rate') !== false) {
                        $error = 'Rate limited by ' . $platform . '. Try again later';
                    }

                    throw new Exception($error . ' | DEBUG: ' . trim((string)$output));
                }
            }

            $fileSize = filesize($outputPath);
            if ($fileSize < 10000) {
                @unlink($outputPath);
                throw new Exception('Downloaded file is too small');
            }

            return array(
                'filename' => $baseName . '.mp4',
                'filepath' => $outputPath,
                'filesize' => $this->formatFileSize($fileSize),
                'platform' => $platform
            );

        } catch (Exception $e) {
            throw $e;
        }
    }

    private function formatFileSize($bytes) {
        $sizes = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($sizes) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $sizes[$pow];
    }
}

$response = array(
    'success' => false,
    'message' => '',
    'filename' => '',
    'filesize' => '',
    'platform' => '',
    'video_url' => '',
    'download_url' => ''
);

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['url']) || empty($input['url'])) {
        throw new Exception('URL is required');
    }

    $url = trim($input['url']);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }

    $downloader = new VideoDownloader();
    $result = $downloader->downloadVideo($url);

    $response['success'] = true;
    $response['filename'] = $result['filename'];
    $response['filesize'] = $result['filesize'];
    $response['platform'] = $result['platform'];
    $response['video_url'] = 'downloads/' . $result['filename'];
    $response['download_url'] = 'download.php?file=' . urlencode($result['filename']);
    $response['message'] = $result['platform'] . ' video downloaded successfully!';

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
