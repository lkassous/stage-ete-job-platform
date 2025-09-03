import { Component } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { Notification } from './components/notification/notification';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, RouterLink, RouterLinkActive, Notification],
  templateUrl: './app.html',
  styleUrl: './app.scss'
})
export class App {
  title = 'CV Filtering System';
}
