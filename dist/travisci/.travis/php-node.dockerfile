FROM juampynr/drupal8ci:latest

RUN curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
RUN apt install -y nodejs xvfb libgtk-3-dev libnotify-dev libgconf-2-4 libnss3 libxss1 libasound2

RUN wget "https://www.sqlite.org/2020/sqlite-autoconf-3310100.tar.gz"  \
    && tar xzf sqlite-autoconf-3310100.tar.gz \
    && cd sqlite-autoconf-3310100 && ./configure --disable-static --enable-fts5 --enable-json1 CFLAGS="-g -O2 -DSQLITE_ENABLE_FTS3=1 -DSQLITE_ENABLE_FTS4=1 -DSQLITE_ENABLE_RTREE=1 -DSQLITE_ENABLE_JSON1" \
    && make \
    && make install
