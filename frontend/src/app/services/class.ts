import { Marker } from "leaflet";

export interface Artist {
  id: number;
  name: string;
  description: string;
  image: string;
  thumbnail: string;
  type_music: string;
  events?: ArtistEvent[]
}

export interface ArtistEvent {
  heure_debut: string;
  heure_fin: string;
  type: EventType;
  date: EventDate;
  publish: boolean;
  eventLocation: EventLocation;
}

export interface ProgramArtist {
  id: number;
  name: string;
  description: string;
  image: string;
  thumbnail: string;
  type_music: string;
}

export interface Program {
  id: number;
  heure_debut: string;
  heure_fin: string;
  type: EventType;
  artist: ProgramArtist;
  date: EventDate;
  publish: boolean;
  eventLocation: EventLocation;
}

export interface EventType {
  type: string;
}

export interface EventDate {
  date: string;
}

export interface EventLocation {
  locationName: string;
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

  export interface News {
    id: number;
    title: string;
    content: string;
    publish: boolean;
    push: boolean;
    type: string;
    notificationDate: string;
    notificationEndDate: string;
  }

  export interface Partner {
    id: number;
    name: string;
    url: string;
    image: string;
    type: PartnerType;
    publish:string;
  }

  export interface PartnerType {
    id: number;
    type: string;
  }