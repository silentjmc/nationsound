import { Marker } from "leaflet";

// Classe pour les artistes
export interface Artist {
    id:number,
    name:string,
    description:string,
    type_musique:string,
    photo_artiste:string,
    photo_thumbnail:string,
    date:string, 
    heure_debut:string, 
    heure_fin:string, 
    type_evenement:string,
    scene:string,
    publish:boolean
  }

  export interface CustomEvent {
    target: HTMLSelectElement;
    id: number;
    eventName: string;
  }
  
  //  Classe pour les lieux
  export interface Location {
    id: number;
    locationName: string;
  }
  
  // Classe pour les dates
  export interface DateEvent {
    id: number;
    date: string ;
  }

  // Classe pour les heures
  export interface TimeEvent {
    id: number;
    hour: string ;
  }

  // Classe pour les POI
  export interface Poi {
    type: string;
    name: string;
    text: string;
    lat: number;
    lon: number;
    iconUrl: string;
    marker?: Marker
  }

  export interface PoiType {
    type: string;
    exactType: string;
  }