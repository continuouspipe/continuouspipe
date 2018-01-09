FROM node:6.9.1

WORKDIR /app
COPY package.json /app/package.json

# Install app dependencies
RUN npm install

# Bundle app source
COPY . /app

EXPOSE 443

CMD ["node", "src/main.js"]
