FROM mcr.microsoft.com/playwright:v1.31.0-focal AS base

COPY . /e2e

WORKDIR /e2e

RUN npm install --ignore-scripts
RUN npx playwright install --with-deps
USER pwuser

CMD [ "npx", "playwright", "test"]
