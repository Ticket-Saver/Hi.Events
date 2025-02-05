import { useMutation } from '@tanstack/react-query';
import { axios } from '../lib/axios';
import { Ticket } from '../types';

interface CreateTicketData {
    title: string;
    type: string;
    description: string;
    max_per_order: number;
    min_per_order: number;
    sale_start_date: string;
    sale_end_date: string;
    hide_after_sale_end_date: boolean;
    hide_before_sale_start_date: boolean;
    hide_when_sold_out: boolean;
    is_hidden_without_promo_code: boolean;
    show_quantity_remaining: boolean;
    position: string;
    seat_number: string;
    section: string;
    prices: Array<{
        price: number;
        initial_quantity_available: number;
    }>;
    tax_and_fee_ids: number[];
}

export const useCreateTicket = (eventId?: string | number) => {
    return useMutation({
        mutationFn: async (data: CreateTicketData) => {
            const response = await axios.post(`/events/${eventId}/tickets`, data);
            if (!response.data) {
                throw new Error('No data received from server');
            }
            return response.data.data;
        },
        onError: (error) => {
            console.error('Create ticket error:', error);
            throw error;
        }
    });
}; 