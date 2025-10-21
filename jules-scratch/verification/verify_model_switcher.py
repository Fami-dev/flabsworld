
from playwright.sync_api import sync_playwright
import os

def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        # Navigate to the local HTML file
        file_path = os.path.abspath('chat.html')
        page.goto(f'file://{file_path}')

        # Click the model switcher button
        page.click('#model-switcher-trigger')

        # Wait for the modal to be visible
        page.wait_for_selector('#model-switcher-modal', state='visible')

        # Take a screenshot
        page.screenshot(path='jules-scratch/verification/verification.png')

        browser.close()

if __name__ == "__main__":
    run()
