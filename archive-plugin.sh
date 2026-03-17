#!/bin/bash

set -e

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR/flixcdn"
XML_FILE="$PLUGIN_DIR/flixcdn.xml"
OUTPUT_DIR="$SCRIPT_DIR"

if [ ! -d "$PLUGIN_DIR" ]; then
    echo -e "${RED}Error: flixcdn directory not found at $PLUGIN_DIR${NC}"
    exit 1
fi

if [ ! -f "$XML_FILE" ]; then
    echo -e "${RED}Error: flixcdn.xml not found at $XML_FILE${NC}"
    exit 1
fi

VERSION=$(grep -oP '(?<=<version>)[^<]+' "$XML_FILE" 2>/dev/null || grep '<version>' "$XML_FILE" | sed -n 's/.*<version>\(.*\)<\/version>.*/\1/p')

if [ -z "$VERSION" ]; then
    echo -e "${RED}Error: Could not extract version from flixcdn.xml${NC}"
    exit 1
fi

VERSION=$(echo "$VERSION" | tr -d '[:space:]')

ARCHIVE_NAME="flixcdn-v${VERSION}.zip"
ARCHIVE_PATH="$OUTPUT_DIR/$ARCHIVE_NAME"

echo -e "${YELLOW}FlixCDN Plugin Archive Builder${NC}"
echo "=================================="
echo "Version: $VERSION"
echo "Output:  $ARCHIVE_NAME"
echo ""

if [ -f "$ARCHIVE_PATH" ]; then
    echo -e "${YELLOW}Removing existing archive...${NC}"
    rm "$ARCHIVE_PATH"
fi

echo "Creating archive..."
cd "$PLUGIN_DIR"

zip -r ../"$ARCHIVE_NAME" . \
    -x ".idea/*" \
    -x "*.zip" \
    -x ".DS_Store" \
    -x "*/.DS_Store" \
    -x ".git/*" \
    -q

cd "$SCRIPT_DIR"

if [ -f "$ARCHIVE_PATH" ]; then
    ARCHIVE_SIZE=$(du -h "$ARCHIVE_PATH" | cut -f1)
    echo ""
    echo -e "${GREEN}✓ Successfully created archive${NC}"
    echo "=================================="
    echo "File:     $ARCHIVE_NAME"
    echo "Version:  $VERSION"
    echo "Size:     $ARCHIVE_SIZE"
    echo "Location: $OUTPUT_DIR"
else
    echo -e "${RED}✗ Failed to create archive${NC}"
    exit 1
fi
