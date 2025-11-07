const puppeteer = require('puppeteer');

(async () => {
  const base = process.env.TEST_BASE_URL || 'http://localhost/GENTA';
  const studentsUrl = base.replace(/\/$/, '') + '/teacher/dashboard/students';
  console.log('Starting headless smoke test for idle-expiry behavior');
  console.log('Target students URL:', studentsUrl);

  const browser = await puppeteer.launch({ args: ['--no-sandbox','--disable-setuid-sandbox'] });
  const page = await browser.newPage();
  page.setDefaultTimeout(10000);

  try {
    await page.goto(studentsUrl, { waitUntil: 'networkidle2' });
    console.log('Loaded students page');

    // Ensure the Add Student button exists
    const addSel = 'a.btn-add-student';
    await page.waitForSelector(addSel, { timeout: 5000 });
    console.log('Add Student button found — clicking it to open modal (simulating AJAX modal open while unauthenticated)');

    // Listen for navigation — the client code reloads the page when login HTML is returned
    const navPromise = page.waitForNavigation({ timeout: 5000 }).catch(() => null);

    await page.click(addSel);

    const navigation = await navPromise;
    if (navigation) {
      console.log('Navigation detected — page reloaded as expected (session expiry handling).');
      console.log('New URL:', page.url());
      await browser.close();
      process.exit(0);
    }

    // If no navigation, inspect modal body content to see if login form was injected (bad)
    const modalBodySel = '#studentModal .modal-body';
    await page.waitForSelector(modalBodySel, { timeout: 5000 });
    const modalHtml = await page.$eval(modalBodySel, el => el.innerHTML);

    if (/form[^>]+action=["'][^"']*\/users?\/login["']/i.test(modalHtml) || /name=["']?username["']?/i.test(modalHtml) || /name=["']?password["']?/i.test(modalHtml)) {
      console.error('Login form detected inside modal — client did NOT reload top-level page (FAILED).');
      console.error('Modal HTML snippet:', modalHtml.slice(0, 400));
      await browser.close();
      process.exit(2);
    }

    console.log('No navigation and no login form detected inside modal. Modal content appears to have loaded normally.');
    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error('Smoke test encountered an error:', err);
    try { await browser.close(); } catch(e){}
    process.exit(3);
  }
})();
