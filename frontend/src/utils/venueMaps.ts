import map1Seats from '../assets/venue-maps/map1/seats.json';
import map1Template from '../assets/venue-maps/map1/template.json';
import map2Seats from '../assets/venue-maps/map2/seats.json';
import map2Template from '../assets/venue-maps/map2/template.json';

export const getMapTemplate = (mapType: string) => {
  switch (mapType) {
    case 'map1':
      return map1Template;
    case 'map2':
      return map2Template;
    default:
      throw new Error(`Unknown map type: ${mapType}`);
  }
};

export const getMapSeats = (mapType: string) => {
  switch (mapType) {
    case 'map1':
      return map1Seats;
    case 'map2':
      return map2Seats;
    default:
      throw new Error(`Unknown map type: ${mapType}`);
  }
};

export const validateSeatFormat = (mapType: string, seatNumber: string): boolean => {
  const template = getMapTemplate(mapType);
  
  if (mapType === 'map1') {
    return /^[A-E][1-9][0]?$/.test(seatNumber);
  } else if (mapType === 'map2') {
    return /^[1-5][A-J]$/.test(seatNumber);
  }
  
  return false;
}; 