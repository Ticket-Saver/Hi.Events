import {TextInput, TextInputProps, Select, Group, Box} from '@mantine/core';
import {IconSearch, IconX, IconFilterOff} from '@tabler/icons-react';
import classes from './SearchBar.module.scss';
import {useEffect, useState} from "react";
import {SortSelector, SortSelectorProps} from "../SortSelector";
import {t} from "@lingui/macro";
import classNames from "classnames";
import {PaginationData, QueryFilters} from "../../../types.ts";
import {ActionIcon} from "@mantine/core";

interface SearchBarProps extends TextInputProps {
    onClear: () => void;
    sortProps?: SortSelectorProps | undefined,
}

interface SearchBarWrapperProps {
    placeholder?: string,
    setSearchParams: (updates: Partial<QueryFilters>) => void,
    searchParams: Partial<QueryFilters>,
    pagination?: PaginationData,
}

export const SearchBarWrapper = ({
    placeholder,
    setSearchParams,
    searchParams,
    pagination
}: SearchBarWrapperProps) => {
    const positionOptions = [
        { value: 'Izquierda', label: 'Izquierda' },
        { value: 'Centro-Izquierda', label: 'Centro-Izquierda' },
        { value: 'Centro', label: 'Centro' },
        { value: 'Centro-Derecha', label: 'Centro-Derecha' },
        { value: 'Derecha', label: 'Derecha' }
    ];

    const sectionOptions = [
        { value: 'Anarajando', label: 'Anarajando' },
        { value: 'Amarillo', label: 'Amarillo' },
        { value: 'Verde', label: 'Verde' },
        { value: 'Gris', label: 'Gris' },
        { value: 'Rojo', label: 'Rojo' },
        { value: 'Azul', label: 'Azul' },
        { value: 'Fucsia', label: 'Fucsia' }
    ];

    const handleSearch = (value: string) => {
        setSearchParams({
            ...searchParams,
            query: value || '',
            pageNumber: 1
        });
    };

    const handleFilterChange = (field: string, value: string | null) => {
        setSearchParams({
            ...searchParams,
            [field]: value || '',
            pageNumber: 1
        });
    };

    return (
        <Box>
            <Group mb="md" align="flex-end">
                <TextInput
                    style={{ flex: 1 }}
                    placeholder={placeholder}
                    value={searchParams?.query || ''}
                    onChange={(e) => handleSearch(e.target.value)}
                    leftSection={<IconSearch size={16}/>}
                    rightSection={
                        searchParams?.query && ( 
                            <ActionIcon
                                variant="subtle"
                                onClick={() => handleSearch('')}
                                title={t`Clear search`}
                            >
                                <IconX size={16}/>
                            </ActionIcon>
                        )
                    }
                    styles={(theme) => ({
                        input: {
                            '&:focus': {
                                borderColor: theme.colors.blue[5]
                            }
                        }
                    })}
                />
                <Select
                    style={{ minWidth: 150 }}
                    placeholder={t`Position`}
                    value={searchParams?.position || ''}
                    onChange={(value) => handleFilterChange('position', value)}
                    data={positionOptions}
                    clearable
                />
                <Select
                    style={{ minWidth: 150 }}
                    placeholder={t`Section`}
                    value={searchParams?.section || ''}
                    onChange={(value) => handleFilterChange('section', value)}
                    data={sectionOptions}
                    clearable
                    searchable
                />
                <TextInput
                    placeholder={t`Seat Number`}
                    value={searchParams?.seat_number || ''}
                    onChange={(e) => handleFilterChange('seat_number', e.target.value)}
                    style={{ width: 120 }}
                />
                {(searchParams?.position || searchParams?.section || searchParams?.seat_number) && (
                    <ActionIcon
                        variant="subtle"
                        onClick={() => {
                            setSearchParams({
                                ...searchParams,
                                position: '',
                                section: '',
                                seat_number: '',
                                pageNumber: 1
                            });
                        }}
                        title={t`Clear filters`}
                    >
                        <IconFilterOff size={16}/>
                    </ActionIcon>
                )}
            </Group>
        </Box>
    );
};

export const SearchBar = ({sortProps, onClear, value, onChange, ...props}: SearchBarProps) => {
    const [searchValue, setSearchValue] = useState<typeof value>(value);

    useEffect(() => {
        setSearchValue(value);
    }, [value])

    return (
        <div className={classNames(classes.searchBarWrapper, props.className)}>
            <TextInput
                className={classes.searchBar}
                leftSection={<IconSearch size="1.1rem" stroke={1.5}/>}
                radius="sm"
                size="md"
                value={searchValue}
                {...props}
                onChange={(event) => {
                    setSearchValue(event.currentTarget.value);
                    if (onChange) {
                        onChange(event);
                    }
                }}
                rightSection={<IconX aria-label={t`Clear Search Text`}
                                     color={'#ddd'}
                                     style={{cursor: 'pointer'}}
                                     display={value ? 'block' : 'none'}
                                     onClick={() => onClear()}
                />}
            />

            {sortProps
                && <SortSelector
                    selected={sortProps.selected}
                    options={sortProps.options}
                    onSortSelect={sortProps.onSortSelect}/>
            }
        </div>
    );
};


