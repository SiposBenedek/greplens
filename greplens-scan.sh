#!/usr/bin/env bash
set -euo pipefail

# Greplens CI Scanner
# Usage: GREPLENS_URL=https://your-instance GREPLENS_API_KEY=glp_xxx ./greplens-scan.sh [path]

: "${GREPLENS_URL:?Set GREPLENS_URL to your Greplens instance URL}"
: "${GREPLENS_API_KEY:?Set GREPLENS_API_KEY to your project API key}"

SCAN_PATH="${1:-.}"
RULES_URL="${GREPLENS_URL}/api/rules"
FINDINGS_URL="${GREPLENS_URL}/api/findings"
RESULTS_FILE=$(mktemp)

trap 'rm -f "$RESULTS_FILE"' EXIT

echo "Fetching rules from ${GREPLENS_URL}..."
RULES_FILE=$(mktemp --suffix=.yaml)
trap 'rm -f "$RESULTS_FILE" "$RULES_FILE"' EXIT

HTTP_CODE=$(curl -s -w "%{http_code}" -o "$RULES_FILE" \
  -H "X-Api-Key: ${GREPLENS_API_KEY}" \
  "${RULES_URL}")

if [ "$HTTP_CODE" != "200" ]; then
  echo "Failed to fetch rules (HTTP ${HTTP_CODE})" >&2
  exit 1
fi

echo "Running opengrep scan on ${SCAN_PATH}..."
opengrep scan --config "$RULES_FILE" --json "$SCAN_PATH" > "$RESULTS_FILE" 2>/dev/null || true

COUNT=$(jq '.results | length' "$RESULTS_FILE" 2>/dev/null || echo "0")
echo "Found ${COUNT} findings."

if [ "$COUNT" -eq 0 ]; then
  echo "No findings to upload."
  exit 0
fi

echo "Uploading findings to ${GREPLENS_URL}..."
UPLOAD_CODE=$(curl -s -w "%{http_code}" -o /dev/null \
  -X POST "${FINDINGS_URL}" \
  -H "X-Api-Key: ${GREPLENS_API_KEY}" \
  -H "Content-Type: application/json" \
  -d @"$RESULTS_FILE")

if [ "$UPLOAD_CODE" != "200" ]; then
  echo "Failed to upload findings (HTTP ${UPLOAD_CODE})" >&2
  exit 1
fi

echo "Done. ${COUNT} findings uploaded."
