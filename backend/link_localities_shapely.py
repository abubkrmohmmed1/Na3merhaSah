#!/usr/bin/env python3
"""
Accurate locality-to-state linking using Shapely for geometric containment.
This script correctly matches localities to their containing states.
"""

import os
import sys
import psycopg2
import traceback
from shapely import wkb, wkt
from shapely.geometry import Point, Polygon, MultiPolygon

# Database connection parameters
DB_PARAMS = {
    'host': '127.0.0.1',
    'port': 5432,
    'database': 'namerha_sah_db',
    'user': 'postgres',
    'password': '1412'
}

def safe_print(text):
    """Print text safely handling encoding issues"""
    try:
        print(text)
    except UnicodeEncodeError:
        # Fallback to ASCII-only representation
        print(text.encode('ascii', 'replace').decode('ascii'))

def load_state_boundaries():
    """Load state boundaries from database as Shapely geometries"""
    conn = psycopg2.connect(**DB_PARAMS)
    cursor = conn.cursor()
    
    cursor.execute("""
        SELECT id, properties->>'name' as name, ST_AsBinary(geom) as geom_wkb
        FROM geo_features 
        WHERE layer_id = (
            SELECT id FROM map_layers WHERE name = 'الولايات'
        )
    """)
    
    states = {}
    for row in cursor.fetchall():
        state_id, name, geom_wkb = row
        if geom_wkb:
            try:
                geom = wkb.loads(geom_wkb, hex=True)
                states[state_id] = {'name': name, 'geom': geom}
                safe_print(f"Loaded state: {name} (ID: {state_id}) with geometry type: {geom.geom_type}")
            except Exception as e:
                safe_print(f"Error loading geometry for state {name}: {str(e)[:100]}")
    
    cursor.close()
    conn.close()
    return states

def load_localities():
    """Load localities from database"""
    conn = psycopg2.connect(**DB_PARAMS)
    cursor = conn.cursor()
    
    cursor.execute("""
        SELECT id, properties->>'name' as name, ST_AsBinary(geom) as geom_wkb
        FROM geo_features 
        WHERE layer_id = (
            SELECT id FROM map_layers WHERE name = 'المحليات'
        )
        AND parent_id IS NULL
    """)
    
    localities = []
    for row in cursor.fetchall():
        locality_id, name, geom_wkb = row
        if geom_wkb:
            try:
                geom = wkb.loads(geom_wkb, hex=True)
                localities.append({
                    'id': locality_id,
                    'name': name,
                    'geom': geom
                })
            except Exception as e:
                safe_print(f"Error loading geometry for locality {name}: {str(e)[:100]}")
    
    cursor.close()
    conn.close()
    return localities

def link_localities_to_states(states, localities):
    """Link each locality to the state that contains it using proper geometric containment"""
    linked_count = 0
    
    conn = psycopg2.connect(**DB_PARAMS)
    cursor = conn.cursor()
    
    for locality in localities:
        locality_point = locality['geom']
        locality_name = locality['name']
        
        # Find which state contains this locality
        for state_id, state_info in states.items():
            state_geom = state_info['geom']
            
            # Check if state geometry contains locality point
            # For multipolygon states, this works correctly
            try:
                if state_geom.contains(locality_point):
                    # Update the database
                    cursor.execute("""
                        UPDATE geo_features 
                        SET parent_id = %s 
                        WHERE id = %s
                    """, (state_id, locality['id']))
                    
                    safe_print(f"✓ Linked: {locality_name} -> {state_info['name']}")
                    linked_count += 1
                    break
            except Exception as e:
                safe_print(f"Error checking containment for {locality_name}: {str(e)[:50]}")
        else:
            safe_print(f"✗ No state found for locality: {locality_name}")
    
    conn.commit()
    cursor.close()
    conn.close()
    
    return linked_count

def main():
    safe_print("Loading state boundaries from database...")
    states = load_state_boundaries()
    safe_print(f"Loaded {len(states)} states\n")
    
    safe_print("Loading localities from database...")
    localities = load_localities()
    safe_print(f"Loaded {len(localities)} localities\n")
    
    safe_print("Linking localities to states using geometric containment...")
    linked_count = link_localities_to_states(states, localities)
    
    safe_print(f"\nSuccessfully linked {linked_count} localities to states")
    
    # Show final statistics
    conn = psycopg2.connect(**DB_PARAMS)
    cursor = conn.cursor()
    
    safe_print("\n=== Final Statistics ===")
    for state_id, state_info in states.items():
        try:
            cursor.execute("""
                SELECT COUNT(*) 
                FROM geo_features 
                WHERE parent_id = %s
                  AND layer_id = (
                      SELECT id FROM map_layers WHERE name = 'المحليات'
                  )
            """, (state_id,))
            count = cursor.fetchone()[0]
            safe_print(f"{state_info['name']}: {count} localities")
        except Exception as e:
            safe_print(f"Error counting for {state_info['name']}: {str(e)[:50]}")
    
    cursor.close()
    conn.close()

if __name__ == '__main__':
    main()