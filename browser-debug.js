import { chromium } from 'playwright';

async function debugBrowser(url, action = 'console') {
    const context = await chromium.launchPersistentContext('./browser-session-data', {
        headless: false, // Set to true to run in background
        devtools: true,  // Opens DevTools automatically
        viewport: { width: 2560, height: 1440 },
        args: [
            '--ignore-certificate-errors-spki-list',
            '--ignore-certificate-errors',
            '--ignore-ssl-errors'
        ]
    });

    const page = await context.newPage();


    // Listen to console messages
    page.on('console', msg => {
        console.log(`[Browser Console ${msg.type()}]:`, msg.text());
    });

    // Listen to page errors
    page.on('pageerror', error => {
        console.log('[Page Error]:', error.message);
    });

    // Navigate to URL
    await page.goto(url);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Log current URL
    const currentUrl = page.url();
    console.log(`[Browser Debug] Current URL: ${currentUrl}`);

    // Monitor URL changes
    page.on('framenavigated', (frame) => {
        if (frame === page.mainFrame()) {
            console.log(`[Browser Debug] Navigated to: ${frame.url()}`);
        }
    });


    if (action === 'html') {
        const html = await page.content();
        console.log('=== Page HTML ===');
        console.log(html);
    }

    if (action === 'console') {
        // Evaluate JavaScript in the browser context
        const result = await page.evaluate(() => {
            // You can run any JavaScript here
            return {
                title: document.title,
                url: window.location.href,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight,
                    devicePixelRatio: window.devicePixelRatio
                },
                screen: {
                    width: window.screen.width,
                    height: window.screen.height
                },
                cookies: document.cookie,
                localStorage: Object.keys(localStorage),
                sessionStorage: Object.keys(sessionStorage)
            };
        });
        console.log('=== Browser Context ===');
        console.log(JSON.stringify(result, null, 2));
    }

    if (action === 'interactive') {
        console.log('Browser is running. Press Ctrl+C to exit...');
        // Keep browser open for manual debugging
        await new Promise(() => { });
    } else {
        await context.close();
    }
}

// Parse command line arguments
const args = process.argv.slice(2);
const url = args[0] || 'https://trinavo_shop.localhost/';
const action = args[1] || 'interactive';

if (!url.startsWith('http')) {
    console.log('Usage: node browser-debug.js <url> [action]');
    console.log('Actions: html, console, interactive');
    process.exit(1);
}

debugBrowser(url, action).catch(console.error);