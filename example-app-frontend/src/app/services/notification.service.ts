import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
  duration?: number;
}

@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private notificationsSubject = new BehaviorSubject<Notification[]>([]);
  public notifications$ = this.notificationsSubject.asObservable();

  constructor() {}

  /**
   * Afficher une notification de succès
   */
  showSuccess(message: string, duration: number = 5000): void {
    this.addNotification('success', message, duration);
  }

  /**
   * Afficher une notification d'erreur
   */
  showError(message: string, duration: number = 7000): void {
    this.addNotification('error', message, duration);
  }

  /**
   * Afficher une notification d'avertissement
   */
  showWarning(message: string, duration: number = 5000): void {
    this.addNotification('warning', message, duration);
  }

  /**
   * Afficher une notification d'information
   */
  showInfo(message: string, duration: number = 4000): void {
    this.addNotification('info', message, duration);
  }

  /**
   * Supprimer une notification
   */
  removeNotification(id: string): void {
    const currentNotifications = this.notificationsSubject.value;
    const updatedNotifications = currentNotifications.filter(n => n.id !== id);
    this.notificationsSubject.next(updatedNotifications);
  }

  /**
   * Supprimer toutes les notifications
   */
  clearAll(): void {
    this.notificationsSubject.next([]);
  }

  /**
   * Ajouter une nouvelle notification
   */
  private addNotification(type: Notification['type'], message: string, duration: number): void {
    const notification: Notification = {
      id: this.generateId(),
      type,
      message,
      duration
    };

    const currentNotifications = this.notificationsSubject.value;
    this.notificationsSubject.next([...currentNotifications, notification]);

    // Auto-suppression après la durée spécifiée
    if (duration > 0) {
      setTimeout(() => {
        this.removeNotification(notification.id);
      }, duration);
    }
  }

  /**
   * Générer un ID unique pour la notification
   */
  private generateId(): string {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }
}
