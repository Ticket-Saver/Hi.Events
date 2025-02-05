import {useParams} from "react-router-dom";
import {useDisclosure, useLocalStorage} from "@mantine/hooks";
import {Button} from "@mantine/core";
import {IconTicket, IconUpload, IconRefresh} from "@tabler/icons-react";
import {PageTitle} from "../../common/PageTitle";
import {PageBody} from "../../common/PageBody";
import {CreateTicketModal} from "../../modals/CreateTicketModal";
import {TicketsTable} from "../../common/TicketsTable";
import {SearchBarWrapper} from "../../common/SearchBar";
import {ToolBar} from "../../common/ToolBar";
import {useFilterQueryParamSync} from "../../../hooks/useFilterQueryParamSync.ts";
import {QueryFilters} from "../../../types.ts";
import {useGetTickets} from "../../../queries/useGetTickets.ts";
import {TableSkeleton} from "../../common/TableSkeleton";
import {Pagination} from "../../common/Pagination";
import {t} from "@lingui/macro";
import {useUrlHash} from "../../../hooks/useUrlHash.ts";
import { useGetEvent } from "../../../queries/useGetEvent.ts";
import { useState } from 'react';
import { getMapSeats } from '../../../utils/venueMaps';
import map1Seats from '../../../assets/venue-maps/map1/seats.json';
import map2Seats from '../../../assets/venue-maps/map2/seats.json';
import { notifications } from "@mantine/notifications";
import { useCreateTicket } from "../../../queries/useCreateTicket";
import { useQueryClient } from "@tanstack/react-query";

export const Tickets = () => {
    const [searchParams, setSearchParams] = useFilterQueryParamSync();
    const [createModalOpen, {open: openCreateModal, close: closeCreateModal}] = useDisclosure(false);
    const {eventId} = useParams();
    const {data: event} = useGetEvent(eventId);
    const ticketsQuery = useGetTickets(eventId, searchParams as QueryFilters);
    const pagination = ticketsQuery?.data?.meta;
    const tickets = ticketsQuery?.data?.data;
    const enableSorting =
        (Object.keys(searchParams).length === 0) ||
        (
            (searchParams.sortBy === 'order' || searchParams.sortBy === undefined) &&
            (searchParams.query === '' || searchParams.query === undefined)
        );

    const [isCreatingBulk, setIsCreatingBulk] = useState(false);
    const [selectedMap, setSelectedMap] = useState(1);

    const createTicket = useCreateTicket(eventId);
    const queryClient = useQueryClient();

    // Guardar en localStorage los IDs de eventos que ya tienen tickets creados
    const [eventsWithTickets, setEventsWithTickets] = useLocalStorage<string[]>({
        key: 'events-with-tickets',
        defaultValue: []
    });

    const hasCreatedTickets = eventId ? eventsWithTickets.includes(eventId) : false;

    //console.log('Event venue map:', event?.map);

    useUrlHash('create-ticket', () => openCreateModal());

    const handleBulkCreate = async () => {
        if (!event?.map || hasCreatedTickets) return;
        
        try {
            setIsCreatingBulk(true);
            const mapSeats = getMapSeats(event.map);
            const seats = mapSeats.tickets;
            
            const chunk = (arr: any[], size: number) => {
                return Array.from({ length: Math.ceil(arr.length / size) }, (v, i) =>
                    arr.slice(i * size, i * size + size)
                );
            };

            // Reducir el tamaño del lote a 10 tickets
            const batches = chunk(seats, 10);
            let createdCount = 0;
            const totalTickets = seats.length;

            for (const batch of batches) {
                // Procesar cada ticket en el lote secuencialmente
                for (const seat of batch) {
                    const ticketData = {
                        title: seat.title,
                        type: "PAID",
                        description: "",
                        max_per_order: 100,
                        min_per_order: 1,
                        sale_start_date: "",
                        sale_end_date: "",
                        hide_after_sale_end_date: false,
                        hide_before_sale_start_date: false,
                        hide_when_sold_out: false,
                        is_hidden_without_promo_code: false,
                        show_quantity_remaining: false,
                        position: seat.position.toString(),
                        seat_number: seat.seat_number.toString(),
                        section: seat.section,
                        prices: [{
                            price: parseFloat(seat.price) || 0,
                            initial_quantity_available: parseInt(seat.quantity) || 1,
                            label: null
                        }],
                        tax_and_fee_ids: []
                    };

                    await createTicket.mutateAsync(ticketData);
                    createdCount++;

                    // Actualizar progreso
                    if (createdCount % 5 === 0) { // Mostrar progreso cada 5 tickets
                        notifications.show({
                            title: t`Progress`,
                            message: t`Created ${createdCount} of ${totalTickets} tickets`,
                            color: 'blue',
                            autoClose: 2000
                        });
                    }

                    // Esperar 500ms entre tickets
                    await new Promise(resolve => setTimeout(resolve, 500));
                }

                // Esperar 2 segundos entre lotes
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                // Actualizar la lista después de cada lote
                await queryClient.invalidateQueries({
                    queryKey: ['tickets', eventId],
                    refetchType: 'all'
                });
            }

            // Forzar una actualización final
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: ['tickets', eventId],
                    refetchType: 'all'
                }),
                queryClient.refetchQueries({
                    queryKey: ['tickets', eventId],
                    type: 'all'
                })
            ]);

            if (eventId) {
                setEventsWithTickets(prev => [...prev, eventId]);
            }

            // Resetear los filtros y la búsqueda para mostrar todos los tickets
            setSearchParams({});

            notifications.show({
                title: t`Success`,
                message: t`All ${totalTickets} tickets created successfully`,
                color: 'green'
            });

        } catch (error) {
            console.error('Error creating tickets:', error);
            notifications.show({
                title: t`Error`,
                message: t`Failed to create tickets: ${error.message}`,
                color: 'red'
            });
        } finally {
            setIsCreatingBulk(false);
        }
    };

    const handleReset = () => {
        if (eventId) {
            setEventsWithTickets(prev => prev.filter(id => id !== eventId));
            notifications.show({
                title: t`Reset`,
                message: t`You can create tickets again`,
                color: 'blue'
            });
        }
    };

    return (
        <PageBody>
            <PageTitle>{t`Tickets`}</PageTitle>

            <ToolBar searchComponent={() => (
                <SearchBarWrapper
                    placeholder={t`Search by ticket name, section, seat number or position...`}
                    setSearchParams={setSearchParams}
                    searchParams={searchParams}
                    pagination={pagination}
                />
            )}>
                <div className="flex gap-4">
                    {event?.map && (
                        <>
                            <Button 
                                color={'blue'} 
                                size={'sm'} 
                                onClick={handleBulkCreate}
                                loading={isCreatingBulk}
                                disabled={hasCreatedTickets}
                                rightSection={<IconUpload size={16}/>}
                                title={hasCreatedTickets ? t`Tickets already created` : t`Create from template`}
                            >
                                {hasCreatedTickets ? t`Tickets Created` : t`Create from template`}
                            </Button>
                            {hasCreatedTickets && (
                                <Button
                                    color={'yellow'}
                                    size={'sm'}
                                    onClick={handleReset}
                                    rightSection={<IconRefresh size={16}/>}
                                    title={t`Reset to create tickets again`}
                                >
                                    {t`Reset`}
                                </Button>
                            )}
                        </>
                    )}
                    <Button 
                        color={'green'} 
                        size={'sm'} 
                        onClick={openCreateModal} 
                        rightSection={<IconTicket/>}
                    >
                        {t`Create Ticket`}
                    </Button>
                    
              
                </div>
            </ToolBar>

            <TableSkeleton isVisible={!tickets || ticketsQuery.isFetching || !event}/>

            {(tickets && event)
                && (<TicketsTable
                        openCreateModal={openCreateModal}
                        enableSorting={enableSorting}
                        tickets={tickets}
                        event={event}
                    />
                )}

            {!!tickets?.length && (
                <Pagination value={searchParams.pageNumber}
                            onChange={(value) => setSearchParams({pageNumber: value})}
                            total={Number(pagination?.last_page)}
                />
            )}

            {createModalOpen && <CreateTicketModal onClose={closeCreateModal} isOpen={createModalOpen}/>}
        </PageBody>
    );
};

export default Tickets;
