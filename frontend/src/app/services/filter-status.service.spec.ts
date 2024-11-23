import { TestBed } from '@angular/core/testing';

import { FilterStatusService } from './filter-status.service';

describe('FilterStatusService', () => {
  let service: FilterStatusService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(FilterStatusService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
