import requests
import json
import os

def fetch_real_data():
    # Bounding box for Al-Thawra Block 10, Omdurman
    # Approximately around 15.68, 32.48
    bbox = "15.6800,32.4750,15.6950,32.4950"
    overpass_url = "http://overpass-api.de/api/interpreter"
    
    # Query for buildings, named nodes, and shops in the area
    overpass_query = f"""
    [out:json];
    (
      node["name"]({bbox});
      way["building"]({bbox});
      node["shop"]({bbox});
      node["amenity"]({bbox});
    );
    out center;
    """

    print(f"Fetching real data from OpenStreetMap for Al-Thawra Block 10...")
    
    try:
        headers = {'User-Agent': 'NamerhaSah-Addressing-Bot/1.0'}
        response = requests.get(overpass_url, params={'data': overpass_query}, headers=headers, timeout=30)
        response.raise_for_status()
        data = response.json()
    except Exception as e:
        print(f"Error fetching data: {e}")
        return

    # Sort features by location (Lat then Lng) to make numbering somewhat sequential
    data['elements'].sort(key=lambda x: (x.get('lat') or x.get('center', {}).get('lat', 0), x.get('lon') or x.get('center', {}).get('lon', 0)))

    features = []
    residential_counter = 1
    
    for element in data.get('elements', []):
        lat = element.get('lat') or element.get('center', {}).get('lat')
        lng = element.get('lon') or element.get('center', {}).get('lon')
        tags = element.get('tags', {})
        
        # Determine type
        is_landmark = 'name' in tags or 'shop' in tags or 'amenity' in tags or 'office' in tags
        
        if is_landmark:
            addr_type = 'landmark' if 'shop' not in tags else 'commercial'
            # Use actual name or type-based name
            name = tags.get('name') or tags.get('shop') or tags.get('amenity') or "معلم غير مسمى"
            address_str = f"الثورة الحارة العاشرة - {name}"
        else:
            addr_type = 'residential'
            # Sequential numbering for homes
            address_str = f"الثورة الحارة العاشرة مبنى رقم {residential_counter:04d}"
            residential_counter += 1

        if lat and lng:
            features.append({
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": [lng, lat]
                },
                "properties": {
                    "address_str": address_str,
                    "neighborhood": "الثورة الحارة العاشرة",
                    "type": addr_type,
                    "osm_id": element['id']
                }
            })

    geojson = {
        "type": "FeatureCollection",
        "features": features
    }

    output_path = 'c:/namerha_sah/backend/database/data/custom_addresses.json'
    
    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(geojson, f, ensure_ascii=False, indent=2)

    print(f"Successfully imported {len(features)} real points from OpenStreetMap!")

if __name__ == "__main__":
    fetch_real_data()
