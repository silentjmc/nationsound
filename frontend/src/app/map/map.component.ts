import { AfterViewInit, Component, Input, OnDestroy, OnInit } from '@angular/core';
import { MapService } from '../services/map.service';
import { Map, Marker } from 'leaflet';
import { Poi } from '../services/class';
import { Meta, Title } from '@angular/platform-browser';

@Component({
  selector: 'app-map',
  standalone: true,
  imports: [],
  templateUrl: './map.component.html',
  styleUrl: './map.component.css', 
  providers:[MapService]
})

  export class MapComponent implements AfterViewInit, OnDestroy, OnInit{
  @Input() changeTitle: boolean;
  public message: string ='';
  private map?: Map;
  private marker?: Marker;

  // Tableau des marqueurs de la carte
  // Array of map markers
  private poi: Poi[] = [
    {type:'music', name:'Electric Dreams',text:'Scene Electro', lat:48.60555648201025, lon:2.3495254734559055, iconUrl:'./assets/music.png'},
    {type:'music', name:'Harmonic Haven',text:'Scene World Music', lat:48.597874037605095, lon:2.32459941171925, iconUrl:'./assets/music.png'},
    {type:'music', name:'Metal Mayhem',text:'Scene Metal', lat:48.59767145029308, lon:2.335945951148819, iconUrl:'./assets/music.png'},
    {type:'music', name:'Rock Rebellion',text:'Scene Rock', lat:48.605007951530624, lon:2.33492968977739, iconUrl:'./assets/music.png'},
    {type:'music', name:'Urban Rythms',text:'Scene rap', lat:48.6124811749261, lon:2.3506715434933003, iconUrl:'./assets/music.png'},
    {type:'toilet', name:'Toilettes',text:'', lat:48.60233363238804, lon:2.3430253621632375, iconUrl:'./assets/wc.png'},
    {type:'toilet', name:'Toilettes',text:'', lat:48.60209036603576, lon:2.3348296034677185, iconUrl:'./assets/wc.png'},
    {type:'toilet', name:'Toilettes',text:'', lat:48.60991663381126, lon:2.342951330901947, iconUrl:'./assets/wc.png'},
    {type:'food', name:'Rythmes et Saveurs',text:'Food truck', lat:48.603763439230804, lon:2.345204188881376, iconUrl:'./assets/food.png'},
    {type:'food', name:'Gourmet Groove',text:'Food truck', lat:48.609909196330264, lon:2.3462603681052427, iconUrl:'./assets/food.png'},
    {type:'food', name:'Electro Eats',text:'Food truck', lat:48.60210443248682, lon:2.3301960822750165, iconUrl:'./assets/food.png'},
    {type:'firstAid', name:'Pompiers',text:'Point secours', lat:48.59577373892234, lon:2.3328653072426984, iconUrl:'./assets/hospital.png'},
    {type:'firstAid', name:'Pompiers',text:'Point secours', lat:48.60036628860047, lon:2.324265716046009, iconUrl:'./assets/hospital.png'},
    {type:'firstAid', name:'Pompiers',text:'Point secours', lat:48.610739684093346, lon:2.3531312214853335, iconUrl:'./assets/hospital.png'},
    {type:'meet', name:'Pavillon Bleu',text:'Point rencontre', lat:48.60541783463773, lon:2.3265754650662913, iconUrl:'./assets/house.png'},
    {type:'meet', name:'Pavillon Rouge',text:'Point rencontre', lat:48.601984523007744, lon:2.325192894408919, iconUrl:'./assets/house.png'},
    {type:'meet', name:'Pavillon Noir',text:'Point rencontre', lat:48.60021656171927, lon:2.326424893422101, iconUrl:'./assets/house.png'}
  ]

  // Tableau pour gérer les filtres
  // Array to manage filters
  filter: { [key: string]: boolean } = {
    'all': true,
    'music': false,
    'food': false,
    'toilet': false,
    'firstAid': false,
    'meet': false

  };

  // Information pour SEO
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
    // Mettre à jour le titre de la page si nécessaire
    // Update the page title if necessary
    if (this.changeTitle) {
      this.title.setTitle('Plan du Nation Sound Festival 2024 - Localisez vos Scènes et Points de Restauration');
    }
  }
  // Chargement de la carte après l'initialisation du composant
  // Load the map after the component has been initialized
  async ngAfterViewInit() {
    try {
      await this.mapService.leafletLoaded;
      if (this.mapService?.L) {
        // Leaflet est chargé
        // Load the map!
        this.message = 'Map Loaded';
        this.setupMap();
      } else {
        // LeaftLet n'est pas chargé
        // Map not loaded
        this.message = 'Map not loaded';
      }
    } catch (error) {
      console.error('Error loading Leaflet', error);
      this.message = 'Error loading map';
    }
    console.log(this.message);
  }

  // Initialisation de la carte
  // Map initialization
  private setupMap() {
    // Vérifier si 'mapService' et 'L' sont définis
    // Check if 'mapService' and 'L' are defined
    if (this.mapService && this.mapService.L) {
      // Créer la carte dans le container #map
      // Create the map in the #map container
      this.map = this.mapService.L.map('map').setView([48.6045, 2.3400], 14);
      
      // Ajouter une couche de tuiles OpenStreetMap
      // Add an OpenStreetMap tile layer
      this.mapService.L.tileLayer(
        'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        {
          attribution:
            'copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>,' +
            ' Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
        }
      ).addTo(this.map);

      // Ajouter les marqueurs à la carte
      // Add markers to the map
      for (const point of this.poi) {
        let icon = this.mapService?.L?.icon({
          iconUrl: point.iconUrl,
          iconSize: [24,24],
          iconAnchor: [12, 24],
          popupAnchor: [0,-22]
        });
        point.marker = this.mapService?.L?.marker([point.lat, point.lon], {icon: icon}).addTo(this.map);
        point.marker?.bindPopup(`<b>${point.name}</b><br>${point.text}`);
      }
    }
  }

  // Filtrer les points sur la carte
  // Filter points on the map
  setFilter(filterName: string, event: Event) {
    // Mettre à jour le filtre
    // Update the filter
    this.filter[filterName] = (event.target as HTMLInputElement).checked;

    // Vérifier si des checkbox de filtres sont cochés ou non
    // Check if any filter checkboxes are checked
    const isNoneChecked = Object.keys(this.filter)
      .filter(key => key !== 'all')
      .every(key => !this.filter[key]);

    this.filter['all'] = isNoneChecked;

    // Parcourir tous les points
    // Loop through all points
    for (const point of this.poi) {
        if (typeof point.type === 'string' && this.map && point.marker) {
        // Si toutes les checkbox de filtres sont décochés, ajouter tous les marqueurs
        // If all filter checkboxes are unchecked, add all markers
        if (isNoneChecked) {
          this.map.addLayer(point.marker);
        } else {
          // Sinon, supprimer le type de marqueur de la carte
          // Otherwise, remove marker type from the map
          this.map.removeLayer(point.marker);

          // Si le filtre pour ce type de point est activé, ajouter le marqueur à la carte
          // If the filter for this point type is enabled, add the marker to the map
          if (this.filter[point.type]) {
            this.map.addLayer(point.marker);
          }
        }
      }
    }
  }

  ngOnDestroy(): void {
    // Supprimer la balise meta lorsque le composant est détruit
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

}