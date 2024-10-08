export interface Artist {
    id:number,
    name:string,
    description:string,
    type_musique:string,
    photo_artiste:string,
    date:string, 
    heure_debut:string, 
    heure_fin:string, 
    type_evenement:string,
    scene?:string,
    lieu_rencontre?:string,
  }

  export interface CustomEvent {
    target: HTMLSelectElement;
    id: number;
    eventName: string;
  }
  
  export interface Location {
    id: number;
    locationName: string;
  }
  
  export interface DateEvent {
    id: number;
    date: string ;
  }

  export interface TimeEvent {
    id: number;
    hour: string ;
  }

