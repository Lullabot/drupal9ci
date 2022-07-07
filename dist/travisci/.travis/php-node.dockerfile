FROM ghcr.io/lullabot/drupal9ci:latest

RUN curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
RUN apt install -y nodejs xvfb libgtk-3-dev libnotify-dev libgconf-2-4 libnss3 libxss1 libasound2
