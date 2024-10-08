import { Component, inject, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { ScheduleService } from '../services/schedule.service';
import { Artist } from '../services/class';
import { Observable, BehaviorSubject, combineLatest, EMPTY, Subscription } from 'rxjs';
import { catchError, filter, map} from 'rxjs/operators';
import { SortPipe } from '../pipe/sort-by.pipe';
import { CheckboxFilter } from '../models/checkbox-filter';
import { Meta, Title } from '@angular/platform-browser';

@Component({
  selector: 'app-programmation',
  standalone: true,
  imports: [CommonModule, SortPipe],
  templateUrl: './programmation.component.html',
  styleUrls: ['./programmation.component.css']
})

export class ProgrammationComponent implements OnInit {
  // Information pour SEO
  constructor(private meta: Meta, private title: Title) {
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
  private scheduleService = inject(ScheduleService);
  private subscription: Subscription = new Subscription();
  public timeFiltersStart!: string | null;
  public timeFiltersEnd!: string | null;
  errorMessage: string | null = null;

  // Initilisation des données artistes, filtres et application des filtres  
  ngOnInit(): void {
    this.loadArtists();
    this.loadFilters();
    this.applyFilters();
  }
  // Désabonnement de l'observable lors de la destruction du composant
  ngOnDestroy() {
    this.subscription.unsubscribe(); // Unsubscribe when the component is destroyed
  }
  // Récupération des artistes  
  loadArtists() {
    this.artists$ = this.scheduleService.getPosts().pipe(
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    );
  }
  // Récupération des filtres
  loadFilters() {
    if (!this.artists$) {
      console.error("No data available in this.artists$");
      return; // Exit the function if no data is available
    }
    // subscirption à l'observvable pour récupérer et mapper les données
    this.subscription = this.artists$.pipe(
      filter(artists => !!artists),
      map(artists => {
        // Récupération des lieux de rencontre
        const locations = [...new Set(artists.map(artist => artist.scene || artist.lieu_rencontre))];
        this.locationFilters = locations.map((location, index) => ({
          id: index,
          name: location,
          isChecked: false
        } as CheckboxFilter));
        // Récupération des types d'événements
        const events = [...new Set(artists.map(artist => artist.type_evenement))];
        this.eventFilters = events.map((event, index) => ({
          id: index,
          name: event,
          isChecked: false
        } as CheckboxFilter));
        // Récupération des dates
        const dates = [...new Set(artists.map(artist => artist.date))];
        this.dateFilters = dates.map((date, index) => ({
          id: index,
          name: date,
          isChecked: false
        }));
        //  Récupération des heures de début
        const times = [...new Set(artists.map(artist => artist.heure_debut))];
        this.timeFilters = times.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
        }));
        //  Récupération des heures de fin
        const timesEnd = [...new Set(artists.map(artist => artist.heure_fin))];
        this.timeFinalFilters = timesEnd.map((time, index) => ({
          id: index,
          name: time,
          isChecked: false
      }));

      }),
      catchError(error => {
        console.error('Une erreur est survenue lors de la récupération des artistes :', error);
        return EMPTY; // Retourne un observable vide en cas d'erreur
      })
    ).subscribe();
  }
  // Vérification si l'heure de début ou de fin
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
        if (!locations.length && !events.length && !dates.length && !this.timeFiltersStart && !this.timeFiltersEnd) {
          return artists; // Retourner tous les artistes si aucune case à cocher n'est cochée
        }
        // Filtrer les artistes en fonction de la localisation
        if (locations.length) {
          artists = artists.filter(artist => {
            const sceneOrLocation = artist.scene || artist.lieu_rencontre;
            return sceneOrLocation && locations.includes(sceneOrLocation);
          });
        }
        // Filtrer les artistes en fonction du type d'événement
        if (events.length) {
          artists = artists.filter(artist => artist.type_evenement && events.includes(artist.type_evenement));
        }
        // Filtrer les artistes en fonction de la date
        if (dates.length) {
          artists = artists.filter(artist => artist.date && dates.includes(artist.date));
        }
      //  Filtrer les artistes en fonction de l'heure de début et de fin
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
  onTimeStartChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeStart = this.timeFiltersStart;
    this.timeFiltersStart = selectElement.value;
    // Vérifier si l'heure de début est supérieure à l'heure de fin
    if (!this.validateTime()) {
      this.timeFiltersStart = ''; // Réinitialiser à "indifférent" si l'heure de début est supérieure à l'heure de fin
      selectElement.value = this.timeFiltersStart;
      this.errorMessage = "L'heure de début ne peut pas être supérieure à l'heure de fin.";
      console.error(this.errorMessage);
    } else {
      this.errorMessage = null;  // Réinitialiser le message d'erreur
    }
    this.applyFilters();
  }

  //  Gestion du filtre d'heure de fin
  onTimeEndChange(event: Event): void {
    const selectElement = event.target as HTMLSelectElement;
    const lastValueTimeEnd = this.timeFiltersEnd;
    this.timeFiltersEnd = selectElement.value;
    //  Vérifier si l'heure de fin est inférieure à l'heure de début
    if (!this.validateTime()) {
      this.timeFiltersEnd = ''; // Réinitialiser à "indifférent" si l'heure de fin est inférieure à l'heure de début
      selectElement.value = this.timeFiltersEnd;
      this.errorMessage = "L'heure de fin ne peut pas être inférieure à l'heure de début.";
      console.error(this.errorMessage);
    } else {
      this.errorMessage = null;  // Réinitialiser le message d'erreur
    }
    this.applyFilters();
  }
  //  Validation qur le choix l'heure de début ne dépasse pas l'heure de fin ert inversement
  validateTime(): boolean {
    if (this.timeFiltersStart && this.timeFiltersEnd) {
      return this.timeFiltersStart <= this.timeFiltersEnd;
    }
    return true;
  }
}