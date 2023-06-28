const { matchers } = require('expect-playwright')
const { expect } = require('@playwright/test')

expect.extend(matchers)

process.env.PLAYWRIGHT_EXPERIMENTAL_FEATURES = '1'

module.exports = {
  retries: process.env.CI ? 1 : 0,
  reporter: [ ['allure-playwright'], ['list'], ['html', { open: 'never', outputFolder: 'reports' }]],
  projects: [
    {
      name: 'e2e',
      outputDir: 'test-results',
      testMatch: '**/*.e2e.test.js',
      timeout: 100000,
      expect: {
        timeout: 10 * 1000,
      },
      use: {
        baseURL: process.env.URL,
        browsers: ['chromium'],
        viewport: { width: 1440, height: 900 },
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
        bypassCSP: true,
        launchOptions: {
          args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-web-security',
            '--disable-gpu',
            '--disable-dev-shm-usage'
          ],
          headless: true
        }
      }
    }
  ]
}
