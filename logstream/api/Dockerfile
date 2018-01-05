FROM node:6.9.1

COPY package.json /app/package.json
WORKDIR /app

# Install app dependencies
RUN npm install

# Bundle app source
COPY . /app

EXPOSE 443

CMD ["node", "/app/main.js"]
