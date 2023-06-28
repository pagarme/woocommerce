FROM mcr.microsoft.com/playwright:v1.31.0-focal AS base

COPY ./helper /e2e
COPY ./tests /e2e
COPY ./package.json /e2e
COPY ./playwright.config.js /e2e

WORKDIR /e2e

RUN npm install --ignore-scripts
RUN npx playwright install --with-deps
USER pwuser

CMD [ "npx", "playwright", "test"]
