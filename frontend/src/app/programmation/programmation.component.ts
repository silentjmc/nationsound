import { Component, inject, OnInit, OnDestroy  } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { EventService } from '../services/event.service';
import { Artist } from '../services/class';
import { Observable, BehaviorSubject, combineLatest, EMPTY, Subscription } from 'rxjs';
import { catchError, filter, map} from 'rxjs/operators';
import { SortPipe } from '../pipe/sort-by.pipe';
import { CheckboxFilter } from '../models/checkbox-filter';
import { Meta, Title } from '@angular/platform-browser';
import { RouterLink, RouterModule } from '@angular/router';

@Component({
  selector: 'app-programmation',
  standalone: true,
  imports: [CommonModule, SortPipe, RouterModule,RouterLink],
  templateUrl: './programmation.component.html',
  styleUrls: ['./programmation.component.css']
})

export class ProgrammationComponent implements OnInit, OnDestroy  {
  // Information pour SEO
  // Information for SEO
  //constructor(private meta: Meta, private title: Title, private scheduleService: ScheduleService) {
  constructor(private meta: Meta, private title: Title, private scheduleService: EventService) {
    title.setTitle("Programmation du Nation Sound Festival 2024 - Horaires et Artistes");
    meta.addTags([
      { name: 'description', content: 'Consultez la programmation complète du Nation Sound Festival 2024. Retrouvez les horaires et les artistes pour chaque scène : métal, rock, rap/urban, world et électro. Préparez votre agenda pour ne rien manquer !' }
    ]);
  }

  http = inject(HttpClient);
  artists$!: Observable<Artist[]>;
  filteredArtists$!: Observable<Artist[]>;
  public locationFilters!: CheckboxFilter[];
  public eventFilters!: CheckboxFilter[];
  public dateFilters!: CheckboxFilter[];
  public timeFilters!: CheckboxFilter[];
  public timeFinalFilters!: CheckboxFilter[];
  private locationFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private eventFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private dateFiltersApplied$ = new BehaviorSubject<string[]>([]);
  private subscription: Subscription = new Subscription();
  public timeFiltersStart!: string | null;
  public timeFiltersEnd!: string | null;
  errorMessage: string | null = null;

  // Initilisation des données artistes, filtres et application des filtres
  // Initialization of artists, filters and application of filters  
  ngOnInit(): void {
    //  Récupération des données artistes depuis le service
    //  Get artist data from the service
    this.artists$ = this.scheduleService.artists$;
    //  Chargement des filtres
    //  Load filters
    this.loadFilters();
    this.applyFilters(); 
  }
  // Désabonnement de l'observable lors de la destruction du composant
  // Unsubscribe from the observable when the component is destroyed
  ngOnDestroy() {
    // Se désinscrire lorsque le composant est détruit
    // Unsubscribe when the component is destroyed
    this.subscription.unsubscribe(); 
    // Supprimer la balise meta lorsque le composant est détruit
    // Remove the meta tag when the component is destroyed
    this.meta.removeTag("name='description'");
  }

  // Récupération des filtres
  // Get filters
  loadFilters() {
    if (!this.artists$) {
      // Sortir de la fonction si aucune donnée n'est disponible
      // Exit the function if no data is available
      console.error("No data available in this.artists$");
      return; 
    }
    // subscription à l'observable pour récupérer et mapper les données
    // subscribe to the observable to get and map the data
    this.subscription = this.artists$.pipe(
      filter(artists => !!artists),
      map(artists => {
        // Récupération des lieux de rencontre
        // Get meeting places
        //const locations = [...new Set(artists.map(artist => artist.scene || artist.lieu_rencontre))];
        console.log('Artsites', artists); 
        const locations = [...new Set(artists.map(artist => artist.scene))];
        this.locationFilters = locations.map((location, index) => ({
          id: index,
          name: location,
          isChecked: false
        } as CheckboxFilter));
        // Récupération des types d'événements
        // Get event types
        const events = [...new Set(artists.map(artist => artist.type_evenement))];
        this.eventFilters = events.map((event, index) => ({
          id: index,
          name: event,
          isChecked: false
        } as CheckboxFilter));
        // Récupération des dates
        // Get dates
        const dates = [...new Set(artists.map(artist => artist.date))];
        this.dateFilters = dates.map((date, index) => ({
          id: index,
          name: date,
          isChecked: false
        }));
        //  Récupération des heures de début
        // Get start times
        const times = [...new Set(artists.map(artist => artist.heure_debut))];
        this.timeFilters = times.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
        }));
        //  Récupération des heures de fin
        // Get end times
        const timesEnd = [...new Set(artists.map(artist => artist.heure_fin))];
        this.timeFinalFilters = timesEnd.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
      }));

      }),
      catchError(error => {
        // Retourne un observable vide en cas d'erreur
        // Return an empty observable in case of error
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; 
      })
    ).subscribe();
  }
  // Vérification si l'heure de début ou de fin
  // Check if the time is start or end
  isTimeInRange(artistTimeStart: string, startTime: string, endTime: string): boolean {
    const [startHour, startMinute] = startTime.split(':').map(Number);
    const [endHour, endMinute] = endTime.split(':').map(Number);
    const [artistStartHour, artistStartMinute] = artistTimeStart.split(':').map(Number);
    if (artistStartHour < startHour || artistStartHour > endHour) {
        return false; 
    }
    if (artistStartHour === startHour && artistStartMinute < startMinute) {
        return false; 
    }
    if (artistStartHour === endHour && artistStartMinute > endMinute) {
        return false; 
    }

    return true;
  }
  //  
  applyFilters() {
    this.filteredArtists$ = combineLatest([
      this.artists$,
      this.locationFiltersApplied$,
      this.eventFiltersApplied$,
      this.dateFiltersApplied$,
    ]).pipe(
      map(([artists, locations, events, dates]) => {
        // Retourner tous les artistes si aucune case à cocher n'est cochée
        // Return all artists if no checkbox is checked
        if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
          return artists; 
        }
        // Filtrer les artistes en fonction de la localisation
        // Filter artists based on location
        if (locations.length) {
          artists = artists.filter(artist => {
            const sceneOrLocation = artist.scene;
            return sceneOrLocation && locations.includes(sceneOrLocation);
          });
        }
        // Filtrer les artistes en fonction du type d'événement
        // Filter artists based on event type
        if (events.length) {
          artists = artists.filter(artist => artist.type_evenement && events.includes(artist.type_evenement));
        }
        // Filtrer les artistes en fonction de la date
        // Filter artists based on date
        if (dates.length) {
          artists = artists.filter(artist => artist.date && dates.includes(artist.date));
        }
      //  Filtrer les artistes en fonction de l'heure de début et de fin
      //  Filter artists based on start and end time
      if (this.timeFiltersStart || this.timeFiltersEnd) {
        artists = artists.filter(artist => {
            const artistTimeStart = artist.heure_debut;
            const artistTimeEnd = artist.heure_fin;
            const startTime = this.timeFiltersStart ? this.timeFiltersStart : '00:01';
            const endTime = this.timeFiltersEnd ? this.timeFiltersEnd : '23:59';
            return this.isTimeInRange(artistTimeStart, startTime, endTime) && this.isTimeInRange(artistTimeEnd, startTime, endTime);
        });
    }
        return artists;
      })
    );
  }
  // Gestion du filtre de localisation
  // Location filter management
  locationChanged(filter: string | null): void {
    const currentFilters = this.locationFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.locationFiltersApplied$.next(currentFilters);
    }
  }
  // Gestion du filtre d'événement
  // Event filter management
  eventChanged(filter: string | null): void {
    const currentFilters = this.eventFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.eventFiltersApplied$.next(currentFilters);
    }
  }
  //  Gestion du filtre de date
  // Date filter management
  dateChanged(filter: string | null): void {
    const currentFilters = this.dateFiltersApplied$.getValue();
    if (filter) {
      const idx = currentFilters.indexOf(filter);
      if (idx >= 0) {
        currentFilters.splice(idx, 1);
      } else {
        currentFilters.push(filter);
      }
      this.dateFiltersApplied$.next(currentFilters);
    }
  }

  // Gestion du filtre d'heure de début
  // Start time filter management
  onTimeStartChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeStart = this.timeFiltersStart;
    this.timeFiltersStart = selectElement.value;
    // Vérifier si l'heure de début est supérieure à l'heure de fin
    // Check if the start time is greater than the end time
    if (!this.validateTime()) {
      this.timeFiltersStart = ''; // Réinitialiser à "indifférent" si l'heure de début est supérieure à l'heure de fin - Reset to "indifferent" if the start time is greater than the end time
      selectElement.value = this.timeFiltersStart;
      this.errorMessage = "L'heure de début ne peut pas être supérieure à l'heure de fin.";
      console.error(this.errorMessage);
    } else {
      // Réinitialiser le message d'erreur
      // Reset the error message
      this.errorMessage = null;  
    }
    this.applyFilters();
  }

  //  Gestion du filtre d'heure de fin
  // End time filter management
  onTimeEndChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeEnd = this.timeFiltersEnd;
    this.timeFiltersEnd = selectElement.value;
    //  Vérifier si l'heure de fin est inférieure à l'heure de début
    // Check if the end time is less than the start time
    if (!this.validateTime()) {
      this.timeFiltersEnd = ''; // Réinitialiser à "indifférent" si l'heure de fin est inférieure à l'heure de début - Reset to "indifferent" if the end time is less than the start time
      selectElement.value = this.timeFiltersEnd;
      this.errorMessage = "L'heure de fin ne peut pas être inférieure à l'heure de début.";
      console.error(this.errorMessage);
    } else {
      // Réinitialiser le message d'erreur
      // Reset the error message
      this.errorMessage = null;  
    }
    this.applyFilters();
  }
  //  Validation qur le choix l'heure de début ne dépasse pas l'heure de fin ert inversement
  //  Validation that the choice of start time does not exceed the end time and vice versa
  validateTime(): boolean {
    if (this.timeFiltersStart && this.timeFiltersEnd) {
      return this.timeFiltersStart <= this.timeFiltersEnd;
    }
    return true;
  }
}