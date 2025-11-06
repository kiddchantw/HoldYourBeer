<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth 設定檢查</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #4285f4;
            padding-bottom: 10px;
        }
        h2 {
            color: #666;
            margin-top: 30px;
        }
        .config-item {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #4285f4;
        }
        .config-label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .config-value {
            font-family: 'Courier New', monospace;
            color: #0066cc;
            word-break: break-all;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin-top: 0;
            color: #856404;
        }
        .instructions ol {
            padding-left: 20px;
        }
        .instructions li {
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px 10px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #357ae8;
        }
        .copy-button {
            background: #34a853;
            cursor: pointer;
            border: none;
            margin-left: 10px;
            font-size: 12px;
            padding: 5px 10px;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 90%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Google OAuth 設定檢查</h1>

        <h2>📋 當前 Laravel 設定</h2>

        <div class="config-item">
            <span class="config-label">APP_URL:</span>
            <span class="config-value">{{ $appUrl }}</span>
        </div>

        <div class="config-item">
            <span class="config-label">Client ID:</span>
            <span class="config-value">{{ $clientId }}</span>
            <button class="copy-button" onclick="copyToClipboard('{{ $clientId }}')">複製</button>
        </div>

        <div class="config-item">
            <span class="config-label">Redirect URI:</span>
            <span class="config-value">{{ $redirectUri }}</span>
            <button class="copy-button" onclick="copyToClipboard('{{ $redirectUri }}')">複製</button>
        </div>

        <h2>🔗 完整 OAuth URL</h2>
        <div class="config-item">
            <span class="config-value" style="font-size: 12px;">{{ $fullOAuthUrl }}</span>
        </div>

        <h2>📝 OAuth 參數</h2>
        @foreach($oauthParams as $key => $value)
        <div class="config-item">
            <span class="config-label">{{ $key }}:</span>
            <span class="config-value">{{ $value }}</span>
        </div>
        @endforeach

        <div class="config-item" style="background: #fff3cd; border-left-color: #ffc107;">
            <span class="config-label">⚠️ 實際發送給 Google 的 redirect_uri：</span>
            <span class="config-value" style="color: #856404; font-weight: bold; font-size: 16px;">{{ $actualRedirectUri }}</span>
            <button class="copy-button" onclick="copyToClipboard('{{ $actualRedirectUri }}')">複製這個值</button>
            <br><small style="color: #856404; margin-top: 10px; display: block;">
                ⚠️ <strong>這個值必須與 Google Cloud Console 中設定的完全一致</strong>（包括大小寫、斜線等）
            </small>
        </div>

        <div class="instructions">
            <h3>⚙️ Google Cloud Console 設定步驟</h3>
            <ol>
                <li>前往 <a href="https://console.cloud.google.com/apis/credentials" target="_blank" style="color: #0066cc; font-weight: bold;">Google Cloud Console - Credentials ↗</a></li>
                <li>選擇你的 OAuth 2.0 Client ID（Client ID: <code style="font-size: 11px;">{{ substr($clientId, 0, 20) }}...</code>）</li>
                <li>在「<strong>Authorized JavaScript origins</strong>」新增：
                    <br><code style="background: #fff3cd; padding: 8px; display: inline-block; margin: 5px 0;">{{ $appUrl }}</code>
                    <button class="copy-button" onclick="copyToClipboard('{{ $appUrl }}')">複製</button>
                </li>
                <li>在「<strong>Authorized redirect URIs</strong>」新增：
                    <br><code style="background: #fff3cd; padding: 8px; display: inline-block; margin: 5px 0;">{{ $actualRedirectUri }}</code>
                    <button class="copy-button" onclick="copyToClipboard('{{ $actualRedirectUri }}')">複製</button>
                    <br><small style="color: #dc3545;">⚠️ 確認沒有尾隨斜線 <code>/</code>，協議是 <code>http://</code></small>
                </li>
                <li>點擊「<strong>儲存</strong>」（SAVE）</li>
                <li><strong>等待 5-10 分鐘</strong>讓 Google 更新設定</li>
                <li>清除瀏覽器 cookie 後重新測試</li>
            </ol>
        </div>

        <div class="instructions success">
            <h3>✅ 設定完成後</h3>
            <p>測試 Google 登入功能：</p>
            <a href="{{ route('social.redirect', ['provider' => 'google']) }}" class="button">
                🔐 測試 Google 登入
            </a>
            <a href="{{ route('login') }}" class="button">
                ← 返回登入頁面
            </a>
        </div>

        <h2>🔍 系統檢查</h2>
        <div class="config-item">
            <span class="config-label">/etc/hosts 設定：</span>
            <span class="config-value">
                需要包含：<code>127.0.0.1 local.holdyourbeers.com</code>
                <br>
                <small>執行：<code>ping local.holdyourbeers.com</code> 檢查</small>
            </span>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('已複製到剪貼簿: ' + text);
            }, function(err) {
                alert('複製失敗');
            });
        }
    </script>
</body>
</html>
