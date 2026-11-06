#!/usr/bin/env python3
"""
Script to link localities to states using Shapely for accurate geometric containment.
Uses parent_id foreign key in geo_features table to establish hierarchy.
"""

import os
import sys
import django
from django.conf import settings

# Add the backend directory to Python path
sys.path.append('/app/backend')

# Configure Django settings
os.environ.setdefault('DJANGO_SETTINGS_MODULE', 'config.settings')
django.setup

from django.db import connection
from shapely import wkb, wkt
from shapely.geometry import Point, Polygon, MultiPolygon

def load_state_boundaries():
    """Load state boundaries from database as Shapely geometries"""
    with connection.cursor() as cursor:
        cursor.execute("""
            SELECT id, properties->>'name' as name, ST_AsBinary(geom) as geom_wkb
            FROM geo_features 
            WHERE map_layer_id = (
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
                except Exception as e:
                    print(f"Error loading geometry for state {name}: {e}")
        
        return states

def load_localities():
    """Load localities from database"""
    with connection.cursor() as cursor:
        cursor.execute("""
            SELECT id, properties->>'name' as name, ST_AsBinary(geom) as geom_wkb
            FROM geo_features 
            WHERE map_layer_id = (
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
                    print(f"Error loading geometry for locality {name}: {e}")
        
        return localities

def link_localities_to_states(states, localities):
    """Link each locality to the state that contains it"""
    linked_count = 0
    
    for locality in localities:
        locality_point = locality['geom']
        locality_name = locality['name']
        
        # Find which state contains this locality
        for state_id, state_info in states.items():
            state_geom = state_info['geom']
            
            # Check if state geometry contains locality point
            if state_geom.contains(locality_point):
                # Update the database
                with connection.cursor() as cursor:
                    cursor.execute("""
                        UPDATE geo_features 
                        SET parent_id = %s 
                        WHERE id = %s
                    """, (state_id, locality['id']))
                
                print(f"Linked: {locality_name} -> {state_info['name']}")
                linked_count += 1
                break
        else:
            print(f"No state found for locality: {locality_name}")
    
    return linked_count

def main():
    print("Loading state boundaries...")
    states = load_state_boundaries()
    print(f"Loaded {len(states)} states")
    
    print("\nLoading localities...")
    localities = load_localities()
    print(f"Loaded {len(localities)} localities")
    
    print("\nLinking localities to states...")
    linked_count = link_localities_to_states(states, localities)
    
    print(f"\nSuccessfully linked {linked_count} localities to states")

if __name__ == '__main__':
    main()