import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ProgrammationFilterComponent } from './programmation-filter.component';

describe('ProgrammationFilterComponent', () => {
  let component: ProgrammationFilterComponent;
  let fixture: ComponentFixture<ProgrammationFilterComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProgrammationFilterComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(ProgrammationFilterComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
