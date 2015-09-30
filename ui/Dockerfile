FROM node

RUN mkdir -p /app
WORKDIR /app

# Install prerequisites CLI tools
RUN npm install -g grunt-cli
RUN npm install -g bower

# Install compass
RUN apt-get update -y && apt-get install -y ruby ruby-dev build-essential && \
    gem install compass

# Install Node
ADD package.json /app/package.json
RUN npm install

# Install bower dependencies
ADD .bowerrc /app/.bowerrc
ADD bower.json /app/bower.json
RUN bower install --config.interactive=false --allow-root

# Add application code
ADD . /app

# Set the environment to the development one
ENV NODE_ENV development

# Port 80 for server
# Port 35729 for livereload
EXPOSE 80 35729

# Add running permissions
RUN chmod +x /app/docker/run.sh
CMD ["/app/docker/run.sh"]
