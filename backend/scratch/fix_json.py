import json
import sys

f_path = 'c:/namerha_sah/backend/database/data/custom_addresses.json'

try:
    with open(f_path, 'r', encoding='utf-8') as f:
        data = json.load(f)
    
    with open(f_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=2)
    
    print("Success: JSON is valid and has been reformatted.")
except Exception as e:
    print(f"Error validating JSON: {e}")
    # If there's an error, try to find where it is
    try:
        with open(f_path, 'r', encoding='utf-8') as f:
            content = f.read()
            # Try to pinpoint the error by loading chunks or using a more robust parser
            pass 
    except:
        pass
