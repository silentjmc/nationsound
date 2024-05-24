import { AfterViewInit, Component } from '@angular/core';
import { MapService } from '../services/map.service';
import { Map, Polygon, Circle, Marker } from 'leaflet';
import { Poi } from '../services/class';

@Component({
  selector: 'app-map',
  standalone: true,
  imports: [],
  templateUrl: './map.component.html',
  styleUrl: './map.component.css', 
  providers:[MapService]
})

export class MapComponent implements AfterViewInit{
  public message: string ='';
  private map?: Map;
  private marker?: Marker;
  private poi: Poi[] = [
    {type:'music', name:'Scene 1', lat:48.60555648201025, lon:2.3495254734559055, iconUrl:'../../assets/music.png'},
    {type:'music', name:'Scene 2', lat:48.597874037605095, lon:2.32459941171925, iconUrl:'../../assets/music.png'},
    {type:'music', name:'Scene 3', lat:48.59767145029308, lon:2.335945951148819, iconUrl:'../../assets/music.png'},
    {type:'music', name:'Scene 4', lat:48.605007951530624, lon:2.33492968977739, iconUrl:'../../assets/music.png'},
    {type:'music', name:'Scene 5', lat:48.6124811749261, lon:2.3506715434933003, iconUrl:'../../assets/music.png'},
    {type:'toilet', name:'Toilettes', lat:48.60233363238804, lon:2.3430253621632375, iconUrl:'../../assets/wc.png'},
    {type:'toilet', name:'Toilettes', lat:48.60209036603576, lon:2.3348296034677185, iconUrl:'../../assets/wc.png'},
    {type:'toilet', name:'Toilettes', lat:48.60991663381126, lon:2.342951330901947, iconUrl:'../../assets/wc.png'},
    {type:'food', name:'Food truck', lat:48.603763439230804, lon:2.345204188881376, iconUrl:'../../assets/food.png'},
    {type:'food', name:'Food truck', lat:48.609909196330264, lon:2.3462603681052427, iconUrl:'../../assets/food.png'},
    {type:'food', name:'Food truck', lat:48.60210443248682, lon:2.3301960822750165, iconUrl:'../../assets/food.png'},
    {type:'firstAid', name:'Pompiers', lat:48.59577373892234, lon:2.3328653072426984, iconUrl:'../../assets/hospital.png'},
    {type:'firstAid', name:'Pompiers', lat:48.60036628860047, lon:2.324265716046009, iconUrl:'../../assets/hospital.png'},
    {type:'firstAid', name:'Pompiers', lat:48.610739684093346, lon:2.3531312214853335, iconUrl:'../../assets/hospital.png'},
    {type:'meet', name:'Pavillon', lat:48.60541783463773, lon:2.3265754650662913, iconUrl:'../../assets/house.png'},
    {type:'meet', name:'Pavillon', lat:48.601984523007744, lon:2.325192894408919, iconUrl:'../../assets/house.png'},
    {type:'meet', name:'Pavillon', lat:48.60021656171927, lon:2.326424893422101, iconUrl:'../../assets/house.png'}
  ]
  private filter: 'all' | 'music' | 'food' | 'toilet' | 'firstAid' | 'meet' = 'all';

  constructor(private mapService: MapService) {}

  async ngAfterViewInit() {
    await this.mapService.leafletLoaded;
    if (this.mapService?.L) {
      // Leaflet is loaded - load the map!
      this.message = 'Map Loaded';

      this.setupMap();
    } else {
      // When the server renders it, it'll show this.
      this.message = 'Map not loaded';
    }
  }

  private setupMap() {
    // Create the map in the #map container
    if (this.mapService && this.mapService.L) {
      // this.map = this.mapService.L.map('map');
      // Create the map in the #map container
      this.map = this.mapService.L.map('map').setView([48.6045, 2.3400], 14);
      
      // Add a tilelayer
      this.mapService.L.tileLayer(
        'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        {
          attribution:
            'copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>,' +
            ' Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
        }
      ).addTo(this.map);

      let defaultIcon = this.mapService?.L?.icon({
        iconUrl: '../assets/marker-icon.png', // Remplacez par le chemin de l'icône de marqueur par défaut de Leaflet dans votre projet
        shadowUrl: null
      });

      for (const point of this.poi) {
        let icon = this.mapService?.L?.icon({
          iconUrl: point.iconUrl,
          iconSize: [24,24],
          iconAnchor: [12, 24],
          popupAnchor: [0,-22]
        });
        point.marker = this.mapService?.L?.marker([point.lat, point.lon], {icon: icon}).addTo(this.map);
        point.marker?.bindPopup(`<b>${point.name}</b><br>${point.type}`);
      }
    }
  }

  public setFilter(filter: 'all' | 'music' | 'food' | 'toilet' | 'firstAid'| 'meet') {
    this.filter = filter;
  
    // Mettez à jour la visibilité des marqueurs en fonction du filtre
    for (const point of this.poi) {
      if (this.filter === 'all' || this.filter === point.type) {
        if (this.map && point.marker) {
          point.marker.addTo(this.map);
        }
      } else {
        if (this.map && point.marker) {
          this.map.removeLayer(point.marker);
        }
      }
    }
  }
}