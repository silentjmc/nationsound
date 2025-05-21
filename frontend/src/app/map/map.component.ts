import { Component, Input, OnDestroy, OnInit } from '@angular/core';
import { MapService } from '../services/map.service';
import { Map, Marker } from 'leaflet';
import { Poi } from '../services/class';
import { Meta, Title } from '@angular/platform-browser';
import { Observable, Subscription, EMPTY } from 'rxjs';
import { CheckboxFilter } from '../models/checkbox-filter';
import { catchError, filter, map} from 'rxjs/operators';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-map',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './map.component.html',
  styleUrl: './map.component.css', 
  providers:[MapService]
})

  export class MapComponent implements OnDestroy, OnInit{
  @Input() changeTitle: boolean;
  private map?: Map;
  private poiEa$!: Observable<Poi[]>;
  public filters!: CheckboxFilter[];
  private subscription: Subscription = new Subscription(); 
  private poiEa: Poi[] = [];  
  private filter: { [key: string]: boolean } = { };

  // Information for SEO
  constructor(
    private meta: Meta,
    private title: Title,
    private mapService: MapService
  ) {
    meta.addTags([
      { name: 'description', content: 'Explorez le plan du Nation Sound Festival 2024. Découvrez les emplacements des scènes, des points de restauration et des zones de détente. Préparez votre visite pour une expérience optimale !' }
    ]);
    this.changeTitle = true;
  }

  ngOnInit(): void {
    this.poiEa$ = this.mapService.poiEa$;
    this.subscription = this.poiEa$.subscribe(poiEas => {
      this.poiEa = poiEas;
      setTimeout(() => {
        this.setupMap();
      }, 100);
    });
    this.loadFilters();
    // Update the page title if necessary
    if (this.changeTitle) {
      this.title.setTitle('Plan du Nation Sound Festival 2024 - Localisez vos Scènes et Points de Restauration');
    }
  }

  loadFilters() { 
    this.subscription = this.poiEa$.pipe(
      filter(poiEa => !!poiEa),
         map(poiEa => {
          const uniqueTypes = [...new Set(poiEa.map(poi => poi.type))];
          //const filters = Array.from(uniqueTypes).map((type, index) => { 
          this.filters = uniqueTypes.map((type, index) => ({   
            //return {
              id: index,
              name: type,
              isChecked: false
           //} as CheckboxFilter;
        //});
          }));
        //this.filters = filters;

      }),
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY;
      })
    ).subscribe();
  }

  // Map initialization
  private setupMap() {
    if (this.mapService && this.mapService.L) {
      this.map = this.mapService.L.map('map').setView([48.6045, 2.3400], 14);
     
      // Add an OpenStreetMap tile layer
      this.mapService.L.tileLayer(
        'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        {
          attribution:
            'copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>,' +
            ' Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
        }
      ).addTo(this.map);

      // Add markers to the map
      for (const point of this.poiEa) {
        let icon = this.mapService?.L?.icon({
          iconUrl: point.iconUrl,
          iconSize: [24,24],
          iconAnchor: [12, 24],
          popupAnchor: [0,-22]
        });
        point.marker = this.mapService?.L?.marker([point.lat, point.lon], {icon: icon}).addTo(this.map);
        point.marker?.bindPopup(`<b>${point.name}</b><br>${point.text}`);
        let altText = `${point.name}`;
        let markerIcon = (point.marker as any)._icon;
        if (markerIcon) {
          markerIcon.alt = altText;
        }
      }
    }
  }

  // Filter points on the map
  setFilter(filterName: string, event: Event) {
    // Update the filter
    this.filter[filterName] = (event.target as HTMLInputElement).checked;

    // Check if any filter checkboxes are checked
    const isNoneChecked = Object.keys(this.filter)
      //.filter(key => key !== 'all')
      .every(key => !this.filter[key]);

    //this.filter['all'] = isNoneChecked;

    // Loop through all points and show or hide them based on the filter and if nothing is checked all points are shown
    for (const point of this.poiEa) {
      if (typeof point.type === 'string' && this.map && point.marker) {
        if (isNoneChecked) {
          this.map.addLayer(point.marker);
        } else {
          this.map.removeLayer(point.marker);
          if (this.filter[point.type]) {
            this.map.addLayer(point.marker);
          }
        }
      }
    }
  }

  ngOnDestroy(): void {
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }
}