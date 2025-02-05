import {useQuery} from "@tanstack/react-query";
import {GenericPaginatedResponse, IdParam, QueryFilters, Ticket} from "../types.ts";
import {axios} from "../lib/axios";

export const GET_TICKETS_QUERY_KEY = 'getTickets';

export const useGetTickets = (eventId?: string | number, filters?: Partial<QueryFilters>) => {
    return useQuery({
        queryKey: ['tickets', eventId, filters],
        queryFn: async () => {
            const params = {
                page: filters?.pageNumber || 1,
                per_page: 20,
                query: filters?.query || '',
                sort_by: filters?.sortBy || '',
                sort_direction: filters?.sortDirection || '',
            }; 

            const response = await axios.get(`/events/${eventId}/tickets`, { params });
            return response.data;
        },
        enabled: !!eventId
    });
};