import { Marker } from "leaflet";

export interface Artist {
  idArtist: number;
  nameArtist: string;
  contentArtist: string;
  imageArtist: string;
  thumbnail: string;
  typeMusic: string;
  events?: ArtistEvent[]
}

export interface ArtistEvent {
  heureDebut: string;
  heureFin: string;
  type: EventType;
  date: EventDate;
  publishEvent: boolean;
  eventLocation: EventLocation;
}

export interface ProgramArtist {
  idArtist: number;
  nameArtist: string;
  contentArtist: string;
  imageArtist: string;
  thumbnail: string;
  typeMusic: string;
}

export interface Program {
  idEvent: number;
  heureDebut: string;
  heureFin: string;
  type: EventType;
  artist: ProgramArtist;
  date: EventDate;
  publishEvent: boolean;
  eventLocation: EventLocation;
}

export interface EventType {
  nameType: string;
}

export interface EventDate {
  date: string;
}

export interface EventLocation {
  nameEventLocation: string;
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
  idNews: number;
  titleNews: string;
  contentNews: string;
  publishNews: boolean;
  push: boolean;
  typeNews: string;
  notificationDate: string;
  notificationEndDate: string;
}

export interface Partner {
  idPartner: number;
  namePartner: string;
  url: string;
  imagePartner: string;
  typePartner: PartnerType;
  publishPartner:string;
}

export interface PartnerType {
  idPartnerType: number;
  titlePartnerType: string;
}