FROM node

COPY package.json /app/package.json
WORKDIR /app

# Install app dependencies
RUN npm install

# Bundle app source
COPY . /app

EXPOSE 80

CMD ["node", "/app/main.js"]
