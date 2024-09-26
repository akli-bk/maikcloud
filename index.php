<?php
// 处理CORS
header("Access-Control-Allow-Origin: *");

// 检查是否传递 URL
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        echo file_get_contents($url);
        exit;
    } else {
        http_response_code(400);
        echo "Invalid URL";
        exit;
    }
}

// 获取必应每日壁纸
$bingWallpaper = file_get_contents("https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1");
$bingData = json_decode($bingWallpaper);
$backgroundUrl = "https://www.bing.com" . $bingData->images[0]->url;

// 域名配置
$domains = [
    ["name" => "全球主站域名【www.unboundly.net】", "url" => "https://www.unboundly.net", "details" => "本站永久域名，可能遭遇GFW导致大陆地区封锁。"],
    ["name" => "国内最新域名【unboundly.net】", "url" => "https://unboundly.net", "details" => "大陆地区最新可访问域名。"]
];
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>域名延迟检测</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f0f8ff; /* 淡蓝色背景 */
            color: #333; /* 深灰色文字 */
            display: flex; /* 使用 flexbox */
            justify-content: center; /* 水平居中 */
            align-items: center; /* 垂直居中 */
        }
        .container {
            max-width: 900px; /* 最大宽度 */
            min-width: 700px;
            width: 100%; /* 100%宽度 */
            background-color: white; /* 白色背景 */
            padding: 20px; /* 内边距 */
            border-radius: 8px; /* 圆角 */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* 阴影 */
        }
        h1 {
            text-align: center; /* 标题居中 */
            color: #007bff; /* 蓝色主色调 */
        }
        .announcement {
            background-color: rgba(0, 123, 255, 0.1); /* 浅蓝色背景 */
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .domain {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
            background-color: rgba(0, 0, 0, 0.05); /* 浅灰色背景 */
        }
        .details {
            font-size: 0.9em;
            color: #666; /* 较浅的文字颜色 */
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            color: black;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: red; font-weight: bold;  text-align: center;">为防止失联，请收藏本页面！</h1>
        <div class="announcement" id="announcement">
            正在测试域名延迟，请稍等...
            <span id="countdown">10</span>秒后跳转至速度最快的域名
        </div>
    
        <div id="results"></div>
    
        <div id="modal" class="modal">
            <div class="modal-content">
                <p id="modal-message"></p>
            </div>
    </div>
    </div>    
    
    
    

    <script>
        const originalUrl = window.location.href; // 当前访问地址
        const queryParams = new URLSearchParams(window.location.search); // 获取查询参数
        const path = window.location.hash || ''; // 获取路径

        const domains = <?php echo json_encode($domains); ?>.map(domain => ({
            name: domain.name,
            url: domain.url + path + '?' + queryParams.toString(),
            details: domain.details,
            delay: ''
        }));

        const resultsDiv = document.getElementById('results');
        let countdown = 10; // 倒计时
        let activeDomains = 0; // 可用域名计数

        function displayResults() {
            resultsDiv.innerHTML = ''; // 清空结果
            domains.forEach(domain => {
                resultsDiv.innerHTML += `
                    <div class="domain normal">
                        ${domain.name}: ${domain.delay} 
                        <div class="details">${domain.details}</div>
                    </div>`;
            });
        }

        function checkDelay() {
            domains.forEach(domain => {
                const startTime = Date.now();
                fetch(`index.php?url=${encodeURIComponent(domain.url)}`)
                    .then(response => {
                        const delay = Date.now() - startTime;
                        if (response.ok) {
                            domain.delay = `正常延迟 ${delay} ms`;
                            activeDomains++;
                        } else {
                            domain.delay = '超时';
                        }
                    })
                    .catch(() => {
                        domain.delay = '超时';
                    })
                    .finally(displayResults);
            });
        }

        function countdownTimer() {
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                showModal();
            } else {
                document.getElementById('countdown').innerText = countdown;
                countdown--;
            }
        }

        function showModal() {
            if (activeDomains > 0) {
                const bestDomain = domains.reduce((prev, current) => {
                    const prevDelay = parseInt(prev.delay) || Infinity;
                    const currentDelay = parseInt(current.delay) || Infinity;
                    return (currentDelay < prevDelay) ? current : prev;
                });

                if (bestDomain) {
                    document.getElementById('modal-message').innerText = 
                        `正在跳转到延迟最低的域名: ${bestDomain.name}`;
                    setTimeout(() => {
                        window.open(bestDomain.url, '_self');
                    }, 5000);
                }
            } else {
                document.getElementById('modal-message').innerText = 
                    "没有可用域名，请检查您的设置。";
            }
            document.getElementById('modal').style.display = 'flex'; // 显示弹窗
        }

        // 开始检测和倒计时
        checkDelay();
        const countdownInterval = setInterval(countdownTimer, 1000);
    </script>
</body>
</html>
