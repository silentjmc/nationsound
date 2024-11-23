import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { CheckboxFilter } from '../models/checkbox-filter';

@Injectable({
  providedIn: 'root'
})
export class FilterStatusService {
  private locationFilters = new BehaviorSubject<CheckboxFilter[]>([]);
  private eventFilters = new BehaviorSubject<CheckboxFilter[]>([]);
  private dateFilters = new BehaviorSubject<CheckboxFilter[]>([]);
  private timeStart = new BehaviorSubject<string | null>(null);
  private timeEnd = new BehaviorSubject<string | null>(null);

  getLocationFilters() {
    return this.locationFilters.asObservable();
  }

  setLocationFilters(filters: CheckboxFilter[]) {
    this.locationFilters.next(filters);
  }

  getEventFilters() {
    return this.eventFilters.asObservable();
  }

  setEventFilters(filters: CheckboxFilter[]) {
    this.eventFilters.next(filters);
  }

  getDateFilters() {
    return this.dateFilters.asObservable();
  }

  setDateFilters(filters: CheckboxFilter[]) {
    this.dateFilters.next(filters);
  }

  getTimeStart() {
    return this.timeStart.asObservable();
  }

  setTimeStart(time: string | null) {
    this.timeStart.next(time);
  }

  getTimeEnd() {
    return this.timeEnd.asObservable();
  }

  setTimeEnd(time: string | null) {
    this.timeEnd.next(time);
  }

  // Méthode pour sauvegarder tous les filtres d'un coup
  saveAllFilters(state: {
    locationFilters: CheckboxFilter[],
    eventFilters: CheckboxFilter[],
    dateFilters: CheckboxFilter[],
    timeStart: string | null,
    timeEnd: string | null
  }) {
    this.locationFilters.next(state.locationFilters);
    this.eventFilters.next(state.eventFilters);
    this.dateFilters.next(state.dateFilters);
    this.timeStart.next(state.timeStart);
    this.timeEnd.next(state.timeEnd);
  }
}