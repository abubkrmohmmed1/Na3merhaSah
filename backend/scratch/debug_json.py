import json
import sys

f_path = 'c:/namerha_sah/backend/database/data/custom_addresses.json'

try:
    with open(f_path, 'r', encoding='utf-8') as f:
        json.load(f)
    print("Success: JSON is valid.")
except json.JSONDecodeError as e:
    print(f"Error at line {e.lineno}, column {e.colno}: {e.msg}")
    # Read the file and print the context
    with open(f_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()
        start = max(0, e.lineno - 5)
        end = min(len(lines), e.lineno + 5)
        for i in range(start, end):
            prefix = ">> " if i + 1 == e.lineno else "   "
            print(f"{prefix}{i+1}: {lines[i].strip()}")
except Exception as e:
    print(f"General error: {e}")
