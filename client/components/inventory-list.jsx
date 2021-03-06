import React from 'react';
import InventoryTable from './inventory-table';
import ItemForm from './item-form';
import Button from './button';
import LoadingSpinner from './loading-spinner';

class InventoryList extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      inventoryList: [],
      unitList: [],
      showSpinner: true
    };
    this.addItem = this.addItem.bind(this);
    this.deleteItem = this.deleteItem.bind(this);
    this.editItem = this.editItem.bind(this);
  }

  componentDidMount() {
    this.getTableData(this.props.categoryId);
  }

  getTableData(categoryId) {
    fetch(`api/inventory?categoryId=${categoryId}`)
      .then(res => res.json())
      .then(data => this.setState({ inventoryList: data.inventory, unitList: data.units, showSpinner: false }));
  }

  addItem(item) {
    item.categoryId = this.props.categoryId;
    const fetchConfig = {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(item)
    };
    fetch('api/inventory', fetchConfig)
      .then(res => res.json())
      .then(newItem => this.setState({
        inventoryList: this.state.inventoryList.concat(newItem)
      })
      );
  }

  deleteItem(id) {
    const fetchConfig = {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ itemId: id })
    };
    fetch('api/inventory', fetchConfig)
      .then(res => res.json())
      .then(data => this.getTableData(this.props.categoryId));
  }

  editItem(itemUpdate) {
    itemUpdate.amountString = `${itemUpdate.amount} ${this.state.unitList[itemUpdate.unitId - 1].unitName}`;
    const fetchConfig = {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(itemUpdate)
    };
    fetch('api/inventory', fetchConfig)
      .then(() => this.getTableData(this.props.categoryId))
      .catch(error => console.error(error.message));
  }

  render() {
    let pageContent = this.state.showSpinner ? <LoadingSpinner /> : (
      <div className="d-flex flex-row flex-lg-row flex-column-reverse">
        <InventoryTable
          inventory={this.state.inventoryList}
          unitList={this.state.unitList}
          handleEdit={this.editItem}
          handleDelete={this.deleteItem} />
        <ItemForm unitList={this.state.unitList} onSubmit={this.addItem} />
      </div>);
    return (
      <div className='container'>
        <div className="row my-3 ml-3">
          <h2 className='menu-heading'>Inventory List: {this.props.categoryName}</h2>
        </div>
        <div className="row mb-3 ml-3">
          <Button
            text= " Return to categories"
            handleClick = {() => this.props.handleNav('categories')}
            symbol = "fa-arrow-left"
            color = "add-button"/>
        </div>
        {pageContent}
      </div>);
  }
}

export default InventoryList;
