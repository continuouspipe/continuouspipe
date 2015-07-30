FROM meteorhacks/meteord:base

# Copy application
COPY . /app

# Copy endpoint scripts
RUN chmod +x /app/docker/run.sh /app/docker/tcp-port-scan.sh

# Run the Meteor installation
RUN bash $METEORD_DIR/on_build.sh

# Update the entrypoint
ENTRYPOINT ["/bin/bash"]
CMD ["/app/docker/run.sh"]
