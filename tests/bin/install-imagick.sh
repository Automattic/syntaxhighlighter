#!/usr/bin/env bash

# ImageMagick version to use
IMAGEMAGICK_VERSION='7.0.7-22'

install_imagemagick() {
	curl -O "https://www.imagemagick.org/download/releases/ImageMagick-$IMAGEMAGICK_VERSION.tar.gz" -f
	tar xzf "ImageMagick-$IMAGEMAGICK_VERSION.tar.gz"
	rm "ImageMagick-$IMAGEMAGICK_VERSION.tar.gz"
	cd "ImageMagick-$IMAGEMAGICK_VERSION"

	./configure --prefix="$HOME/opt/$TRAVIS_PHP_VERSION"
	make
	make install

	cd "$TRAVIS_BUILD_DIR"
}

# Install ImageMagick if the current version isn't up to date
PATH="$HOME/opt/$TRAVIS_PHP_VERSION/bin:$PATH" convert -v | grep "$IMAGEMAGICK_VERSION" || install_imagemagick

# Debugging
ls "$HOME/opt/$TRAVIS_PHP_VERSION"

# Set up environment variables
export LD_FLAGS="-L$HOME/opt/$TRAVIS_PHP_VERSION/lib"
export LD_LIBRARY_PATH="/lib:/usr/lib:/usr/local/lib:$HOME/opt/$TRAVIS_PHP_VERSION/lib"
export CPATH="$CPATH:$HOME/opt/$TRAVIS_PHP_VERSION/include"

# Install Imagick for PHP
echo "$HOME/opt/$TRAVIS_PHP_VERSION" | pecl install imagick