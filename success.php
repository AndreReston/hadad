<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Initialized - PC Parts Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --neon-green: #22c55e;
            --dark-bg: #020617;
            --terminal-text: #94a3b8;
        }

        body { 
            margin: 0; 
            font-family: 'Courier New', Courier, monospace; 
            background: var(--dark-bg); 
            color: var(--neon-green); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            overflow: hidden;
        }

        /* --- BACKGROUND VIDEO CSS --- */
        #bg-video {
            position: fixed;
            right: 0;
            bottom: 0;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: -1; /* Puts video behind everything */
            object-fit: cover; /* Ensures video covers screen without stretching */
            filter: brightness(0.3) contrast(1.2); /* Makes text more readable */
        }

        /* The Main "Case" Container */
        .system-hub {
            border: 2px solid #1e293b;
            /* Added a slight blur to make it look like glass over the video */
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px); 
            padding: 40px;
            border-radius: 4px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 0 50px rgba(34, 197, 94, 0.1);
            text-align: center;
        }

        /* Scanning Line Animation */
        .system-hub::after {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
            background: rgba(34, 197, 94, 0.2);
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% { top: 0; }
            100% { top: 100%; }
        }

        .status-line {
            margin: 10px 0;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            width: 0;
            animation: typing 0.5s steps(30, end) forwards;
            text-align: left;
        }

        @keyframes typing { from { width: 0 } to { width: 100% } }

        .power-ring {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            border: 5px solid #1e293b;
            border-top: 5px solid var(--neon-green);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
        }

        .power-ring::after {
            content: "OK";
            font-weight: bold;
            font-size: 20px;
            opacity: 0;
            animation: fadeIn 0.2s forwards 1.2s;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); border-color: var(--neon-green); }
        }

        h1 { font-size: 1.5rem; margin-bottom: 5px; color: #fff; }
        .user-tag { color: var(--neon-green); font-weight: bold; text-transform: uppercase; }

        .btn-enter {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            border: 1px solid var(--neon-green);
            color: var(--neon-green);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s;
            opacity: 0;
            animation: fadeIn 0.5s forwards 2s;
        }

        .btn-enter:hover {
            background: var(--neon-green);
            color: var(--dark-bg);
            box-shadow: 0 0 20px var(--neon-green);
        }

        @keyframes fadeIn { to { opacity: 1; } }

        .glitch { animation: glitch 1s linear infinite; }
        @keyframes glitch {
            2%, 64% { transform: translate(2px,0) skew(0deg); }
            4%, 60% { transform: translate(-2px,0) skew(0deg); }
            62% { transform: translate(0,0) skew(5deg); }
        }
    </style>
</head>
<body>

    <video autoplay muted loop playsinline id="bg-video">
        <source src="assets/background.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
    </video>

    <div class="system-hub">
        <div class="power-ring"></div>
        
        <h1 class="glitch">SYSTEM INITIALIZED</h1>
        <div class="status-line" style="animation-delay: 1.2s;">> Establishing secure handshake... DONE</div>
        <div class="status-line" style="animation-delay: 1.4s;">> Loading user profile: <span class="user-tag"><?php echo htmlspecialchars($_SESSION['username'] ?? 'GUEST'); ?></span></div>
        <div class="status-line" style="animation-delay: 1.6s;">> Database sync complete... 100%</div>
        <div class="status-line" style="animation-delay: 1.8s;">> Welcome to PC Parts Hub v2.0.26</div>

        <a href="index.php" class="btn-enter">Enter Mainframe</a>
    </div>

</body>
</html>