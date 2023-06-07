FROM mcr.microsoft.com/playwright:v1.31.0-focal

COPY . /e2e

WORKDIR /e2e

RUN npm install
RUN npx playwright install --with-deps

CMD [ "npx", "playwright", "test"]
