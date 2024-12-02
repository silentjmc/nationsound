import { Injectable, PLATFORM_ID, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject } from 'rxjs';
import { NavigationEnd, Router } from '@angular/router';
import { filter, tap, catchError } from 'rxjs/operators';
import { environment } from '../../environments/environment';
import { News } from './class';
import { isPlatformBrowser } from '@angular/common';;

interface NotificationState {
  newsId: number;
  permanentlyDismissed: boolean;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private readonly STORAGE_KEY = 'notification_states';
  private currentNotification = new BehaviorSubject<News | null>(null);
  private lastNotificationId: number | null = null;

  constructor(private http: HttpClient, private router: Router, @Inject(PLATFORM_ID) private platformId: Object) {
    if (isPlatformBrowser(this.platformId)) {
      this.setupNavigationCheck();

    }
  }

  private setupNavigationCheck() {
    // Check for notifications on startup
    this.checkForNotification();

    // Check for notification on each page change
    this.router.events.pipe(
        filter(event => event instanceof NavigationEnd),
        tap(() => this.checkForNotification())
    ).subscribe();
}

private checkForNotification() {
  let url = `${environment.apiUrl}/api/latestNotification`;
  if (this.lastNotificationId) {
      url += `?since=${this.lastNotificationId}`;
  }

  this.http.get<News>(url).pipe(
      catchError(error => {
          console.error('Erreur lors de la vérification des notifications:', error);
          return [];
      })
  ).subscribe(notification => {
      if (notification && 
          (!this.lastNotificationId || notification.id !== this.lastNotificationId) &&
          !this.isNotificationDismissed(notification.id)) {
          this.lastNotificationId = notification.id;
          this.currentNotification.next(notification);
      }
  });
}

  dismissNotification(newsId: number): void {
    if (!isPlatformBrowser(this.platformId)) return;
    const states = this.getNotificationStates();
    const state: NotificationState = {
      newsId,
      permanentlyDismissed: true
    };

    states[newsId] = state;
    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(states));
    this.currentNotification.next(null);
  }

  private isNotificationDismissed(newsId: number): boolean {
    if (!isPlatformBrowser(this.platformId)) return false;
    const state = this.getNotificationState(newsId);
    if (!state) return false;
    return state.permanentlyDismissed;
  }

  private getNotificationState(newsId: number): NotificationState | null {
    const states = this.getNotificationStates();
    return states[newsId] || null;
  }

  private getNotificationStates(): { [key: number]: NotificationState } {
    if (!isPlatformBrowser(this.platformId)) return {};
    const statesJson = localStorage.getItem(this.STORAGE_KEY);
    return statesJson ? JSON.parse(statesJson) : {};
  }

   getCurrentNotification(): Observable<News | null> {
    return this.currentNotification.asObservable();
}

  refreshNotifications() {
      this.checkForNotification();
  }

  // Method to reset the state of notifications
  resetNotificationState(newsId?: number) {
      if (!isPlatformBrowser(this.platformId)) return;

      if (newsId) {
          const states = this.getNotificationStates();
          delete states[newsId];
          localStorage.setItem(this.STORAGE_KEY, JSON.stringify(states));
      } else {
          localStorage.removeItem(this.STORAGE_KEY);
      }
      this.checkForNotification();
  }
}
