export interface Artist {
    id:number,
    name:string,
    description:string,
    type_musique:string,
    photo_artiste:string,
    date:Date, 
    heure_debut:Date, 
    heure_fin:Date, 
    type_evenement:string,
    scene?:string,
    lieu_rencontre?:string,
  }
/*
  export interface Event {
    id:number,
    date:Date, 
    heure_debut:Date, 
    heure_fin:Date, 
    type_evenement:string,
    scene?:string,
    lieu_rencontre?:string,
    artist: Artist
  }*/